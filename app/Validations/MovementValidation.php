<?php

namespace App\Validations;

class MovementValidation
{
    public static function store($request)
    {
        $request->validate([
			'winery_id' => 'required|exists:wineries,id',
            'fecha' => 'required|date',
            'tipo' => 'required|boolean',
            'detalles' => 'nullable|max:30',
            'total' => 'required',
            'company_id' => 'required',
			
			'items' => 'required|array',
			'items.*.item_id' => 'required|exists:items,id',
            'items.*.cantidad' => 'required|numeric|gt:0',
			'items.*.costo_unitario' => 'required|numeric|gte:0',
			'items.*.costo_total' => 'required|numeric|gte:0',
			'items.*.lot_id' => 'nullable',
        ]);
    }
}
