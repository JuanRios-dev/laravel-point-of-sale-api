<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Cash;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $perPage = $request->input('perPage', 5);
        $pagination = $request->input('pagination', true);
        $cashes = Cash::where('company_id', $companyId);

        $search ? $cashes = SearchCollection::searchGeneric(new Cash, $search, $companyId) : $cashes;

        if ($pagination) {
            $perPage = $request->input('perPage', 5);
            $cashes = $cashes->with('user')->orderBy('id', 'desc')->paginate($perPage);
        } else {
            $cashes = $cashes->with('user')->orderBy('id', 'desc')->get();
        }

        return response()->json(['cashes' => $cashes]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'nombre' => 'required|max:30',
        ]);

        $cash = new Cash([
            'company_id' => $request->company_id,
            'nombre' => $request->nombre,
            'user_id' => Auth::id(),
        ]);
        $cash->save();

        return response()->json(['message' => 'Caja creada exitosamente', 'cash' => $cash], 201);
    }

    public function show($id)
    {
        $cash = Cash::with('register')->findOrFail($id);

        return response()->json(['cash' => $cash]);
    }

    public function open(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $cash = Cash::findOrFail($id);

            if ($cash->estado == 1) {
                return response()->json(['error' => 'La caja ya está abierta'], 400);
            }

            $register = new Register();
            $register->cash_id = $cash->id;
            $register->fecha_apertura = now();
            $register->saldo_apertura = $cash->monto;
            $register->user_apertura_id = Auth::id();
            $register->save();

            $cash->user_id = Auth::id();
            $cash->estado = true;
            $cash->save();

            DB::commit();

            return response()->json(['message' => 'Caja abierta exitosamente']);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['error' => 'Error al abrir la caja: ' . $e->getMessage()], 500);
        }
    }

    public function close(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $cash = Cash::findOrFail($id);

            if ($cash->estado == false) {
                return response()->json(['error' => 'La caja ya está cerrada'], 400);
            }

            $lastRegister = $cash->register()->whereNull('fecha_cierre')->latest()->firstOrFail();

            $lastRegister->fecha_cierre = now();
            $lastRegister->saldo_cierre = $cash->monto;
            $lastRegister->user_cierre_id = Auth::id();
            $lastRegister->save();

            $cash->user_id = Auth::id();
            $cash->estado = false;
            $cash->save();

            DB::commit();

            return response()->json(['message' => 'Caja cerrada exitosamente']);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['error' => 'Error al cerrar la caja: ' . $e->getMessage()], 500);
        }
    }
}
