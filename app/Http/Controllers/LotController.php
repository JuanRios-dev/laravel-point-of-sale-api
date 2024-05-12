<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Lot;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $perPage = $request->input('perPage', 5);
        $pagination = $request->input('pagination', true);
        $lots = Lot::where('company_id', $companyId);

        $search ? $lots = SearchCollection::searchGeneric(new Lot, $search, $companyId) : $lots;

        if ($pagination) {
            $perPage = $request->input('perPage', 5);
            $lots = $lots->orderBy('id', 'desc')->paginate($perPage);
        } else {
            $lots = $lots->orderBy('id', 'desc')->get();
        }

        return response()->json(['lots' => $lots]);
    }

    public function destroy($id)
    {
        $lot = Lot::find($id);

        $lot->delete();

        return response()->json(['message' => 'Lote eliminado y vaciado exitosamente'], 200);
    }
}
