<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Cash;
use App\Models\Item;
use App\Models\Lot;
use App\Models\ProviderInvoice;
use App\Models\Winery;
use App\Validations\ProviderInvoiceValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $provider_invoices = ProviderInvoice::with('provider')->where('company_id', $companyId);

        $search ? $provider_invoices = SearchCollection::searchGeneric(new ProviderInvoice, $search, $companyId) : $provider_invoices;

        $provider_invoices = $provider_invoices->orderBy('id', 'desc')->paginate(5);

        return response()->json(['provider_invoices' => $provider_invoices]);
    }

    public function show($id)
    {
        $provider_invoice = ProviderInvoice::with('provider')->find($id);

        $items = $provider_invoice->items;

        foreach ($items as $item) {
            if ($item->pivot->lot_id !== null) {
                $item->lot = Lot::find($item->pivot->lot_id);
            }
        }

        return response()->json(['provider_invoice' => $provider_invoice]);
    }

    public function store(Request $request)
    {
        ProviderInvoiceValidation::store($request);

        $winery = Winery::where('company_id', $request->company_id)->where('predeterminada', 1)->first();

        if ($winery === null) {
            return response()->json(['error' => 'No existen bodegas, agrega una'], 500);
        }

        DB::beginTransaction();

        try {

            if ($request->cash_id) {
                $cash = Cash::find($request->cash_id);

                if ($cash->monto < $request->total) {
                    return response()->json(['error' => 'No hay suficiente presupuesto en la caja especificada'], 500);
                }

                $cash->monto -= $request->total;
                $cash->save();
            }

            $invoice = new ProviderInvoice($request->except('products'));
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

                    if ($existingPivot) {
                        $cantidad = $existingPivot->pivot->cantidad + $itemData['cantidad'];
                        DB::update('UPDATE item_winery SET cantidad = ? WHERE id = ?', [$cantidad, $existingPivot->pivot->id]);
                    } else {
                        $pivotData = ['cantidad' => $itemData['cantidad'], 'lot_id' => $itemData['lot_id']];
                        $winery->items()->attach($itemData['item_id'], $pivotData);
                    }
                } else {
                    $existingItem = $winery->items()->where('item_id', $itemData['item_id'])
                        ->withPivot('id')
                        ->first();

                    if ($existingItem) {
                        $cantidad = $existingItem->pivot->cantidad + $itemData['cantidad'];
                        DB::update('UPDATE item_winery SET cantidad = ? WHERE id = ?', [$cantidad, $existingItem->pivot->id]);
                    } else {
                        $winery->items()->attach($itemData['item_id'], ['cantidad' => $itemData['cantidad']]);
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Factura creada exitosamente'], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
