<?php

namespace App\Validations;

use Illuminate\Support\Facades\Validator;

class ProviderInvoiceValidation
{
    public static function store($request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'codigo' => 'required|unique:provider_invoices,codigo,NULL,id,company_id,'. $request->input('company_id'),
            'fecha' => 'required|date',
            'formaPago' => 'required|string',
            'subTotal' => 'required|numeric|gte:0',
            'totalImpuestos' => 'required|numeric|gte:0',
            'total' => 'required|numeric|gte:0',
            'descuento' => 'required|numeric|gte:0',
            'valorDescuento' => 'required|numeric|gte:0',
            'cash_id' => 'nullable|exists:cashes,id',
            'observaciones' => 'nullable',
            'company_id' => 'required',

            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.cantidad' => 'required|numeric|gt:0',
            'items.*.precio_unitario' => 'required|numeric|gte:0',
            'items.*.descuento' => 'required|numeric|gte:0',
            'items.*.valor_descuento' => 'required|numeric|gte:0',
            'items.*.subtotal' => 'required|numeric|gte:0',
            'items.*.impuestos' => 'required|numeric|gte:0',
            'items.*.precio_total' => 'required|numeric|gte:0',
            'items.*.lot_id' => 'nullable',
        ]);
    }
}
