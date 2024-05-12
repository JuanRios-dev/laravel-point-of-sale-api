<?php

namespace App\Http\Controllers;

use App\Collections\SearchCollection;
use App\Models\Customer;
use App\Validations\CustomerValidation;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $search = $request->input('search');
        $customers = Customer::where('company_id', $companyId);

        $search ? $customers = SearchCollection::searchGeneric(new Customer, $search, $companyId) : $customers;

        $customers = $customers->orderBy('id', 'desc')->paginate(5);

        return response()->json(['customers' => $customers]);
    }

    public function store(Request $request)
    {
        CustomerValidation::store($request);

        $customer = Customer::create($request->all());

        return response()->json(['message' => 'Cliente creado exitosamente', 'customer' => $customer], 201);
    }

    public function show($id)
    {
        $customer = Customer::find($id);
        return response()->json(['customer' => $customer], 200);

    }

    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);
        CustomerValidation::update($request, $customer);

        $customer->update($request->all());

        return response()->json(['message' => 'Cliente actualizado exitosamente'], 200);
    }

    public function destroy($id)
    {
        $customer = Customer::find($id)->delete();

        return response()->json(['message' => 'Cliente eliminado exitosamente'], 200);
    }
}
