<?php

namespace App\Validations;

class TransferWineryValidation
{
    public static function store($request)
    {
        $request->validate([
			'winery_origen_id' => 'required|exists:wineries,id',
			'winery_destino_id' => 'required|exists:wineries,id',
            'fecha' => 'required|date',
            'detalles' => 'nullable|max:30',
            'company_id' => 'required',
			
			'items' => 'required|array',
			'items.*.item_id' => 'required|exists:items,id',
            'items.*.cantidad' => 'required|numeric|gt:0',
			'items.*.lot_id' => 'nullable',
        ]);
    }
}
