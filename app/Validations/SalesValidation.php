<?php

namespace App\Validations;

use Illuminate\Support\Facades\Validator;

class SalesValidation
{
    public static function store($request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'prefijo' => 'required|max:10',
            'numero' => 'nullable',
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
