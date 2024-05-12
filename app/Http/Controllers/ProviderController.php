<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Provider;
use App\Validations\ProviderValidation;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $providers = Provider::where('company_id', $companyId);

        $search ? $providers = SearchCollection::searchGeneric(new Provider, $search, $companyId) : $providers;

        $providers = $providers->orderBy('id', 'desc')->paginate(5);

        return response()->json(['providers' => $providers]);
    }

    public function store(Request $request)
    {
        ProviderValidation::store($request);

        $provider = Provider::create($request->all());

        return response()->json(['message' => 'Proveedor creado exitosamente', 'provider' => $provider], 201);
    }

    public function show($id)
    {
        $provider = Provider::find($id);
        return response()->json(['provider' => $provider], 200);

    }

    public function update(Request $request, $id)
    {
        $provider = Provider::find($id);
        ProviderValidation::update($request, $provider);

        $provider->update($request->all());

        return response()->json(['message' => 'Proveedor actualizado exitosamente'], 200);
    }

    public function destroy($id)
    {
        $provider = Provider::find($id)->delete();

        return response()->json(['message' => 'Proveedor eliminado exitosamente'], 200);
    }
}
