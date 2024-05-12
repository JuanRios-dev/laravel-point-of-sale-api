<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Winery;
use Illuminate\Http\Request;

class WineryController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $perPage = $request->input('perPage', 5);
        $pagination = $request->input('pagination', true);
        $wineries = Winery::where('company_id', $companyId);

        $search ? $wineries = SearchCollection::searchGeneric(new Winery(), $search, $companyId) : $wineries;

        if ($pagination) {
            $perPage = $request->input('perPage', 5);
            $wineries = $wineries->orderBy('id', 'desc')->paginate($perPage);
        } else {
            $wineries = $wineries->orderBy('id', 'desc')->get();    
        }

        return response()->json(['wineries' => $wineries]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'nombre' => 'required|max:30',
            'descripcion' => 'nullable|max:50',
            'ubicacion' => 'required|max:30',
        ]);

        $companyId = $request->input('company_id');
        $winery = Winery::create($request->all());

        $existingWinery = Winery::where('company_id', $companyId)->where('predeterminada', true)->count();

        if ($existingWinery == 0) {
            $winery->predeterminada = true;
            $winery->save();
        }

        return response()->json(['message' => 'Bodega creada exitosamente', 'winery' => $winery], 201);
    }

    public function show($id)
    {
        $winery = Winery::find($id);
        return response()->json(['winery' => $winery], 200);
    }

    public function update(Request $request, $id)
    {
        $winery = Winery::find($id);

        $request->validate([
            'company_id' => 'required',
            'nombre' => 'required|max:30',
            'descripcion' => 'nullable|max:50',
            'ubicacion' => 'required|max:30',
        ]);

        $winery->update($request->all());

        return response()->json(['message' => 'Bodega actualizada exitosamente'], 200);
    }

    public function default(Request $request, $id)
    {
        $companyId = $request->input('company_id');
        $winery = Winery::where('company_id', $companyId)
            ->findOrFail($id);

        if ($winery->predeterminada) {
            return response()->json(['error' => 'La bodega ya está marcada como predeterminada'], 400);
        }

        $winery->predeterminada = true;
        $winery->save();

        Winery::where('company_id', $companyId)
            ->where('id', '!=', $winery->id)
            ->update(['predeterminada' => false]);

        return response()->json(['message' => 'Bodega predeterminada marcada con éxito']);
    }

    public function destroy($id)
    {
        $winery = Winery::find($id);

        if ($winery->predeterminada) {
            return response()->json(['error' => 'No puedes eliminar la bodega predeterminada.'], 400);
        }

        $winery->delete();

        return response()->json(['message' => 'Bodega eliminada exitosamente'], 200);
    }
}
