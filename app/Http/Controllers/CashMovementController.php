<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Cash;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashMovementController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $cash_movements = CashMovement::where('company_id', $companyId);

        $search ? $cash_movements = SearchCollection::searchGeneric(new CashMovement(), $search, $companyId) : $cash_movements;

        $cash_movements = $cash_movements->orderBy('id', 'desc')->paginate(5);

        return response()->json(['cash_movements' => $cash_movements]);
    }

    public function store(Request $request, $id)
    {

        $request->validate([
            'tipo' => 'required|in:deposito,retiro',
            'monto' => 'required|numeric|min:0',
            'detalles' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $cash = Cash::findOrFail($id);

            if ($cash->estado == 0) {
                return response()->json(['error' => 'La caja debe estar abierta para realizar movimientos'], 400);
            }

            $movement = new CashMovement();
            $movement->cash_id = $cash->id;
            $movement->tipo = $request->tipo;
            $movement->monto = $request->monto;
            $movement->detalles = $request->detalles;
            $movement->user_id = $userId;
            $movement->save();

            if ($request->tipo === 'deposito') {
                $cash->monto += $request->monto;
            } else {

                if ($request->monto > $cash->monto) {
                    return response()->json(['error' => 'El monto a retirar es mayor que el saldo disponible en la caja'], 400);
                }
                $cash->monto -= $request->monto;
            }
            $cash->save();

            DB::commit();

            return response()->json(['message' => 'Movimiento de caja registrado exitosamente']);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['error' => 'Error al registrar el movimiento de caja: ' . $e->getMessage()], 500);
        }
    }
}
