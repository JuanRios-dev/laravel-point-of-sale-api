<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Item;
use App\Models\Lot;
use App\Models\Movement;
use App\Models\Winery;
use App\Validations\MovementValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovementController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $movements = Movement::with('winery')->where('company_id', $companyId);

        $search ? $movements = SearchCollection::searchGeneric(new Movement, $search, $companyId) : $movements;

        $movements = $movements->orderBy('id', 'desc')->paginate(5);

        return response()->json(['movements' => $movements]);
    }

    public function show($id)
    {
        $movement = Movement::with('winery')->find($id);

        $items = $movement->items;

        foreach ($items as $item) {
            if ($item->pivot->lot_id !== null) {
                $item->lot = Lot::find($item->pivot->lot_id);
            }
        }

        return response()->json(['movement' => $movement]);
    }


    public function store(Request $request)
    {
        MovementValidation::store($request);

        $winery = Winery::find($request->winery_id);

        DB::beginTransaction();

        try {
            $movement = new Movement($request->except('items'));
            $movement->save();

            $items = $request->input('items');

            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);

                $existingPivot = $winery->items()->where('item_id', $itemData['item_id'])
                    ->wherePivot('lot_id', $itemData['lot_id'])
                    ->withPivot('id')
                    ->first();

                $movement->items()->attach($item, $itemData);

                if ($existingPivot) {
                    if ($request->tipo) {
                        $cantidad = $existingPivot->pivot->cantidad + $itemData['cantidad'];
                    } else {
                        if ($existingPivot->pivot->cantidad < $itemData['cantidad']) {
                            throw new \Exception('La cantidad de ' . $item->descripcion . ' sobrepasa a lo que tienes en stock: ' . $existingPivot->pivot->cantidad);
                        }
                        $cantidad = $existingPivot->pivot->cantidad - $itemData['cantidad'];
                    }

                    DB::update('UPDATE item_winery SET cantidad = ? WHERE id = ?', [$cantidad, $existingPivot->pivot->id]);
                } else {
                    if ($request->tipo) {
                        $pivotData = ['cantidad' => $itemData['cantidad'], 'lot_id' => $itemData['lot_id']];
                        $winery->items()->attach($item->id, $pivotData);
                    } else {
                        throw new \Exception('El ' . $item->descripcion . ' no tiene stock en ' . $winery->nombre);
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Movimiento registrado exitosamente'], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
