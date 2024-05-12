<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Cash;
use App\Models\Company;
use App\Models\Item;
use App\Models\Lot;
use App\Models\Sale;
use App\Models\Winery;
use App\Validations\SalesValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require_once(app_path('Libraries/code128.php'));

use PDF_Code128;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $sales = Sale::with('customer')->where('company_id', $companyId);

        $search ? $sales = SearchCollection::searchGeneric(new Sale, $search, $companyId) : $sales;

        $sales = $sales->orderBy('id', 'desc')->paginate(5);

        return response()->json(['sales' => $sales]);
    }

    public function show($id)
    {
        $sale = Sale::with('customer')->find($id);

        $items = $sale->items;

        foreach ($items as $item) {
            if ($item->pivot->lot_id !== null) {
                $item->lot = Lot::find($item->pivot->lot_id);
            }
        }

        return response()->json(['sale' => $sale]);
    }

    public function store(Request $request)
    {
        SalesValidation::store($request);

        $winery = Winery::where('company_id', $request->company_id)->where('predeterminada', 1)->first();

        if ($winery === null) {
            return response()->json(['error' => 'No existen bodegas, agrega una'], 500);
        }

        DB::beginTransaction();

        try {

            $user = Auth::user();

            $cash = Cash::where('company_id', $request->company_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$cash) {
                return response()->json(['error' => 'El usuario no tiene una caja asociada o la caja no está abierta'], 500);
            } elseif (!$cash->estado) {
                return response()->json(['error' => 'La caja asociada al usuario está cerrada'], 500);
            }

            $numeroVenta = $this->generateSaleCode($request->company_id, $request->prefijo);
            $request->merge(['numero' => $numeroVenta]);
            $request->merge(['cash_id' => $cash->id]);

            $cash->monto += $request->total;
            $cash->save();

            $invoice = new Sale($request->except('products'));
            $invoice->save();

            $items = $request->input('items');

            foreach ($items as $itemData) {
                $item = Item::find($itemData['item_id']);

                $invoice->items()->attach($item, $itemData);

                if (isset($itemData['lot_id'])) {
                    $existingPivot = $winery->items()->where('item_id', $itemData['item_id'])
                        ->wherePivot('lot_id', $itemData['lot_id'])
                        ->withPivot('id')
                        ->first();

                    $lot = Lot::find($itemData['lot_id']);

                    if ($lot && $lot->fecha_vencimiento < now()) {
                        throw new \Exception("El lote {$lot->numero} del item {$item->nombre} ya está vencido.");
                    }


                    if ($existingPivot->pivot->cantidad >= $itemData['cantidad']) {
                        $cantidad = $existingPivot->pivot->cantidad - $itemData['cantidad'];
                        DB::update('UPDATE item_winery SET cantidad = ? WHERE id = ?', [$cantidad, $existingPivot->pivot->id]);
                    } else {
                        throw new \Exception('No tienes stock del item');
                    }
                } else {
                    $existingItem = $winery->items()->where('item_id', $itemData['item_id'])
                        ->withPivot('id')
                        ->first();

                    if ($existingItem->pivot->cantidad >= $itemData['cantidad']) {
                        $cantidad = $existingItem->pivot->cantidad - $itemData['cantidad'];
                        DB::update('UPDATE item_winery SET cantidad = ? WHERE id = ?', [$cantidad, $existingItem->pivot->id]);
                    } else {
                        throw new \Exception('No tienes stock del item');
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Venta realizada exitosamente', 'sale' => $invoice ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateSaleCode($companyId, $prefijo)
    {
        $numeroVenta = Sale::where('company_id', $companyId)
            ->where('prefijo', $prefijo)
            ->max('numero');

        // Incrementar en 1 el número máximo de ventas con el mismo prefijo
        $numeroVenta++;

        return $numeroVenta;
    }

    public function generatePDF($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);
        $company = Company::findOrFail($sale->company_id);
        $cash = Cash::findOrFail($sale->cash_id);
        $items = $sale->items;

        /* CALCULAR ALTURA */
        $alturaItems = count($items) * 4 * 2;
        $largoInicial = 200;
        $largoTotal = $largoInicial + $alturaItems;

        $pdf = new PDF_Code128('P', 'mm', array(80, $largoTotal));
        $pdf->SetMargins(4, 8, 4);
        $pdf->AddPage();

        // Encabezado y datos de la empresa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", strtoupper($company->nombre)), 0, 'C', false);
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "NIT: $company->nit"), 0, 'C', false);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Direccion: $company->direccion"), 0, 'C', false);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Teléfono: $company->telefono"), 0, 'C', false);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Email: $company->correo"), 0, 'C', false);

        $pdf->Ln(1);
        $pdf->Cell(0, 5, iconv("UTF-8", "ISO-8859-1", "--------------------------------------------------------------------"), 0, 0, 'C');
        $pdf->Ln(5);

        // Fecha y detalles de la venta
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Fecha de impresión: " . date("d/m/y h:i A")), 0, 'C', false);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Caja: $cash->nombre"), 0, 'C', false);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Cajero: " . $cash->user->nombre), 0, 'C', false);

        $pdf->Ln(1);
        $pdf->Cell(0, 5, iconv("UTF-8", "ISO-8859-1", "--------------------------------------------------------------------"), 0, 0, 'C');
        $pdf->Ln(5);

        // Detalles del cliente
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell(0, 10, iconv("UTF-8", "ISO-8859-1", strtoupper("Datos del Cliente")), 0, 'C', false);
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Cliente: " . $sale->customer->nombre_razonsocial), 0, 'C', false);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Documento: " . $sale->customer->tipo_documento . " " . $sale->customer->numero_documento), 0, 'C', false);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "Teléfono: " . $sale->customer->telefono), 0, 'C', false);

        $pdf->Ln(1);
        $pdf->Cell(0, 5, iconv("UTF-8", "ISO-8859-1", "-------------------------------------------------------------------"), 0, 0, 'C');
        $pdf->Ln(3);

        // Detalles de la venta
        $pdf->Cell(10, 5, iconv("UTF-8", "ISO-8859-1", "Cant."), 0, 0, 'C');
        $pdf->Cell(19, 5, iconv("UTF-8", "ISO-8859-1", "Precio"), 0, 0, 'C');
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-1", "Desc."), 0, 0, 'C');
        $pdf->Cell(28, 5, iconv("UTF-8", "ISO-8859-1", "Total"), 0, 0, 'C');

        $pdf->Ln(3);
        $pdf->Cell(72, 5, iconv("UTF-8", "ISO-8859-1", "-------------------------------------------------------------------"), 0, 0, 'C');
        $pdf->Ln(3);

        foreach ($items as $item) {
            if ($item->pivot->lot_id !== null) {
                $item->lot = Lot::find($item->pivot->lot_id);
            }

            // Añadir los detalles del producto a la tabla
            $pdf->MultiCell(0, 4, iconv("UTF-8", "ISO-8859-1", $item->nombre . " " . $item->descripcion), 0, 'C', false);
            $pdf->Cell(10, 4, iconv("UTF-8", "ISO-8859-1", $item->pivot->cantidad), 0, 0, 'C');
            $pdf->Cell(19, 4, iconv("UTF-8", "ISO-8859-1", "$" . number_format($item->pivot->precio_unitario, 2)), 0, 0, 'C');
            $pdf->Cell(19, 4, iconv("UTF-8", "ISO-8859-1", "$" . number_format($item->pivot->valor_descuento, 2)), 0, 0, 'C');
            $pdf->Cell(28, 4, iconv("UTF-8", "ISO-8859-1", "$" . number_format($item->pivot->precio_total, 2)), 0, 0, 'C');
            $pdf->Ln(4);
        }
        $pdf->MultiCell(0, 4, iconv("UTF-8", "ISO-8859-1", "Garantía de fábrica NO APLICA"), 0, 'C', false);
        $pdf->Ln(7);

        // Impuestos & totales
        $pdf->Cell(18, 5, iconv("UTF-8", "ISO-8859-1", ""), 0, 0, 'C');
        $pdf->Cell(22, 5, iconv("UTF-8", "ISO-8859-1", "DESCUENTO"), 0, 0, 'C');
        $pdf->Cell(32, 5, iconv("UTF-8", "ISO-8859-1", "+ " . number_format($sale->valorDescuento)), 0, 0, 'C');
        $pdf->Ln(5);
        $pdf->Cell(18, 5, iconv("UTF-8", "ISO-8859-1", ""), 0, 0, 'C');
        $pdf->Cell(22, 5, iconv("UTF-8", "ISO-8859-1", "SUBTOTAL"), 0, 0, 'C');
        $pdf->Cell(32, 5, iconv("UTF-8", "ISO-8859-1", "+ " . number_format($sale->subTotal)), 0, 0, 'C');
        $pdf->Ln(5);
        $pdf->Cell(18, 5, iconv("UTF-8", "ISO-8859-1", ""), 0, 0, 'C');
        $pdf->Cell(22, 5, iconv("UTF-8", "ISO-8859-1", "IMPUESTOS"), 0, 0, 'C');
        $pdf->Cell(32, 5, iconv("UTF-8", "ISO-8859-1", "+ " . number_format($sale->totalImpuestos)), 0, 0, 'C');
        $pdf->Ln(5);
        $pdf->Cell(72, 5, iconv("UTF-8", "ISO-8859-1", "-------------------------------------------------------------------"), 0, 0, 'C');
        $pdf->Ln(5);
        $pdf->Cell(18, 5, iconv("UTF-8", "ISO-8859-1", ""), 0, 0, 'C');
        $pdf->Cell(22, 5, iconv("UTF-8", "ISO-8859-1", "TOTAL A PAGAR"), 0, 0, 'C');
        $pdf->Cell(32, 5, iconv("UTF-8", "ISO-8859-1", "$ " . number_format($sale->total)), 0, 0, 'C');
        $pdf->Ln(10);
        $pdf->MultiCell(0, 5, iconv("UTF-8", "ISO-8859-1", "*** Precios de productos incluyen impuestos. Para poder realizar un reclamo o devolución debe de presentar este ticket ***"), 0, 'C', false);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 7, iconv("UTF-8", "ISO-8859-1", "Gracias por su compra"), '', 0, 'C');
        $pdf->Ln(9);

        # Codigo de barras #
        $pdf->Code128(5, $pdf->GetY(), $sale->prefijo . $sale->numero, 70, 10);
        $pdf->SetXY(0, $pdf->GetY() + 15);
        $pdf->SetFont('Arial', '', 14);
        $pdf->MultiCell(0, 0, iconv("UTF-8", "ISO-8859-1", $sale->prefijo . $sale->numero), 0, 'C', false);

        // Capturar la salida del PDF en una variable
        ob_start();
        $pdf->Output();
        $pdfData = ob_get_clean();

        // Devolver el PDF como respuesta HTTP con el tipo de contenido adecuado
        return response($pdfData)
            ->header('Content-Type', $largoTotal);
    }
}
