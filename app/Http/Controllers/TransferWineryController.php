<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Item;
use App\Models\Lot;
use App\Models\TransferWinery;
use App\Models\Winery;
use App\Validations\TransferWineryValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferWineryController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $tranferWineries = TransferWinery::where('company_id', $companyId);
        $tranferWineries->with('origen', 'destino');

        $search ? $tranferWineries = SearchCollection::searchGeneric(new TransferWinery, $search, $companyId) : $tranferWineries;

        $tranferWineries = $tranferWineries->orderBy('id', 'desc')->paginate(5);

        return response()->json(['tranferWineries' => $tranferWineries]);
    }

    public function show($id)
    {
        // Obtener la transferencia de bodega por su ID junto con la información de las bodegas de origen y destino
        $tranferWinery = TransferWinery::with('origen', 'destino')->find($id);

        // Verificar si la transferencia de bodega fue encontrada
        if (!$tranferWinery) {
            return response()->json(['error' => 'Transferencia de bodega no encontrada'], 404);
        }

        // Obtener los elementos asociados a la transferencia de bodega
        $items = $tranferWinery->transferItems;

        // Iterar sobre los elementos y, si tienen un ID de lote, obtener la información del lote
        foreach ($items as $item) {
            if ($item->pivot->lot_id !== null) {
                $item->lot = Lot::find($item->pivot->lot_id);
            }
        }

        // Retornar la información de la transferencia de bodega junto con los elementos y las bodegas de origen y destino
        return response()->json(['tranferWinery' => $tranferWinery]);
    }

    public function store(Request $request)
    {
        TransferWineryValidation::store($request);

        $winery = Winery::find($request->winery_id);

        if ($request->winery_origen_id === $request->winery_destino_id) {
            return response()->json(['error' => 'La bodega de origen y destino no pueden ser la misma'], 500);
        }

        DB::beginTransaction();

        try {
            $transfer = new TransferWinery($request->except('items'));
            $transfer->save();

            $items = $request->input('items');

            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $winery_origen = Winery::find($request->winery_origen_id);
                $winery_destino = Winery::find($request->winery_destino_id);

                // Agregar el producto a la transferencia
                $transfer->transferItems()->attach($item, ['cantidad' => $itemData['cantidad'], 'lot_id' => $itemData['lot_id']]);

                $existingPivotOrigen = $winery_origen->items()->where('item_id', $itemData['item_id'])
                    ->wherePivot('lot_id', $itemData['lot_id'])
                    ->withPivot('id')
                    ->first();

                if ($existingPivotOrigen) {
                    if ($existingPivotOrigen->pivot->cantidad < $itemData['cantidad']) {
                        throw new \Exception('La cantidad transferida es superior a la disponible en la bodega de origen para ese producto');
                    }

                    DB::table('item_winery')->where('id', $existingPivotOrigen->pivot->id)->decrement('cantidad', $itemData['cantidad']);
                } else {
                    throw new \Exception('El producto no existe en la bódega de origen');
                }

                // Incrementar la cantidad en la bodega de destino
                $existingPivotDestino = $winery_destino->items()->where('item_id', $itemData['item_id'])
                    ->wherePivot('lot_id', $itemData['lot_id'])
                    ->withPivot('id')
                    ->first();

                if ($existingPivotDestino) {
                    $cantidadDestino = $existingPivotDestino->pivot->cantidad + $itemData['cantidad'];

                    DB::update('UPDATE item_winery SET cantidad = ? WHERE id = ?', [$cantidadDestino, $existingPivotDestino->pivot->id]);
                } else {
                    $winery_destino->items()->attach($itemData['item_id'], ['cantidad' => $itemData['cantidad'], 'lot_id' => $itemData['lot_id']]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Transferencia registrada exitosamente']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
