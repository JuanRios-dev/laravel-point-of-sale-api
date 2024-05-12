<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Item;
use App\Models\Lot;
use App\Models\Winery;
use App\Validations\ItemValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $WineryId = Winery::where('company_id', $companyId)
            ->where('predeterminada', 1)
            ->value('id');

        if ($WineryId) {
            // Consulta para sumar la cantidad solo de la bodega predeterminada
            $items = Item::select('items.*', DB::raw('SUM(CASE WHEN item_winery.winery_id = ' . $WineryId . ' THEN item_winery.cantidad ELSE 0 END) as cantidad_total'))
                ->leftJoin('item_winery', 'items.id', '=', 'item_winery.item_id')
                ->where('items.company_id', $companyId)
                ->groupBy('items.id');

            // Aplicar búsqueda si se proporciona
            if ($search) {
                $items = SearchCollection::searchGeneric(new Item, $search, $companyId);
            }

            $items = $items->orderBy('id', 'desc')->paginate(5);

            return response()->json(['items' => $items]);
        } else {
            // Si no se encuentra una bodega predeterminada, retornar una respuesta vacía
            return response()->json(['items' => []]);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            ItemValidation::store($request);

            if (Winery::where('company_id', $request->company_id)->count() === 0) {
                return response()->json(['error' => 'No tienes bodegas creadas'], 404);
            }

            $winery = Winery::where('company_id', $request->company_id)->where('predeterminada', 1)->first();

            $item = Item::create($request->all());

            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $rutaImagen = $imagen->store('imagenes/items', 'public');
                $item->imagen = Storage::url($rutaImagen);
                $item->save();
            }

            $winery->items()->attach($item->id, ['cantidad' => 0]);

            $lots = $request->input('lots');
            if ($lots && is_array($lots)) {
                foreach ($lots as $lotData) {
                    $lot = new Lot();
                    $lot->numero = $lotData['numero'];
                    $lot->fecha_vencimiento = $lotData['fecha_vencimiento'];
                    $lot->item_id = $item->id;
                    $lot->company_id = $request->input('company_id');
                    $lot->save();

                    $winery->lots()->attach($lot->id, ['item_id' => $item->id, 'cantidad' => 0, 'lot_id' => $lot->id]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Producto creado exitosamente', 'item' => $item], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $item = Item::with('lots')->find($id);

        return response()->json(['item' => $item], 200);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $companyId = $request->input('company_id');

        $items = DB::table('items')
            ->select('items.*', DB::raw('MAX(item_winery.cantidad) as cantidad'), 'item_winery.lot_id', 'lots.numero', 'lots.fecha_vencimiento')
            ->leftJoin('item_winery', 'items.id', '=', 'item_winery.item_id')
            ->leftJoin('wineries', 'item_winery.winery_id', '=', 'wineries.id')
            ->leftJoin('lots', 'item_winery.lot_id', '=', 'lots.id')
            ->where('wineries.company_id', $companyId)
            ->where(function ($query) use ($searchTerm) {
                $query->where('items.nombre', 'LIKE', "%$searchTerm%")
                    ->orWhere('items.codigo', 'LIKE', "%$searchTerm%")
                    ->orWhere('lots.numero', 'LIKE', "%$searchTerm%");
            })
            ->groupBy('items.id', 'item_winery.lot_id', 'lots.numero', 'lots.fecha_vencimiento') // Agregar todas las columnas no agregadas a GROUP BY
            ->get();

        return response()->json(['items' => $items]);
    }

    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        ItemValidation::update($request, $item, null);

        $winery = Winery::where('company_id', $request->company_id)->where('predeterminada', 1)->first();

        foreach ($request->lots as $index => $lotData) {
            if (isset($lotData['id'])) {
                $existingLot = Lot::find($lotData['id']);
                $request->validate([
                    'lots.' . $index . '.numero' => 'required|max:50|unique:lots,numero,' . $existingLot->id . ',id,company_id,' . $request->input('company_id'),
                    'lots.' . $index . '.fecha_vencimiento' => 'required|date',
                ]);

                $existingLot->update($lotData);
            } else {
                $request->validate([
                    'lots.' . $index . '.numero' => 'required|max:50|unique:lots,numero,NULL,id,company_id,' . $request->input('company_id'),
                    'lots.' . $index . '.fecha_vencimiento' => 'required|date',
                ]);
                $lot = new Lot();
                $lot->numero = $lotData['numero'];
                $lot->fecha_vencimiento = $lotData['fecha_vencimiento'];
                $lot->item_id = $item->id;
                $lot->company_id = $request->input('company_id');
                $lot->save();

                $winery->lots()->attach($lot->id, ['item_id' => $item->id, 'cantidad' => 0, 'lot_id' => $lot->id]);
            }
        }

        $item->update($request->except('lots'));

        return response()->json(['message' => 'Producto actualizado exitosamente'], 200);
    }

    public function uploadImage(Request $request, $id)
    {
        $item = Item::find($id);

        $request->validate([
            'imagen' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            if ($item->imagen) {
                $rutaImagenAnterior = public_path(parse_url($item->imagen, PHP_URL_PATH));
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior); // Eliminar la imagen anterior
                }
            }

            $imagen = $request->file('imagen');
            $rutaImagen = $imagen->store('imagenes/items', 'public');
            $item->imagen = Storage::url($rutaImagen);
            $item->save();

            return response()->json(['message' => 'Imagen del producto actualizada exitosamente'], 200);
        }

        return response()->json(['error' => 'No se proporcionó ningún archivo de imagen'], 400);
    }

    public function destroy($id)
    {
        $item = Item::find($id);

        if ($item->imagen) {
            $rutaImagen = public_path(parse_url($item->imagen, PHP_URL_PATH));
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
        }

        $item->delete();

        return response()->json(['message' => 'Producto eliminado exitosamente'], 200);
    }
}
