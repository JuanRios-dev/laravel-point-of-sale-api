<?php

namespace App\Validations;

class ItemValidation
{
    public static function store($request)
    {
        $request->validate([
            'codigo' => 'required|max:50|unique:items,codigo,NULL,id,company_id,' . $request->input('company_id'),
            'nombre' => 'required|max:30',
            'descripcion' => 'nullable|max:50',
            'imagen' => 'nullable|image|mimes:jpeg,png|max:3000',
            'tipo' => 'required|in:Inventariable,No Inventariable,Servicio',
            'iva_compra' => 'required',
            'iva_venta' => 'required',
            'precio' => 'required',
            'categoria' => 'nullable',
            'company_id' => 'required',

            'lots' => 'nullable|array',
            'lots.*.numero' => 'required|max:50|unique:lots,numero,NULL,id,company_id,' . $request->input('company_id'),
            'lots.*.fecha_vencimiento' => 'required|date',
        ]);
    }

    public static function update($request, $item)
    {
        $request->validate([
            'codigo' => 'required|max:50|unique:items,codigo,' . $item->id . ',id,company_id,' . $request->input('company_id'),
            'nombre' => 'required|max:30',
            'descripcion' => 'nullable|max:50',
            'imagen' => 'nullable||image|mimes:jpeg,png|max:3000',
            'tipo' => 'required|in:Inventariable,No Inventariable,Servicio',
            'iva_compra' => 'required',
            'iva_venta' => 'required',
            'precio' => 'required',
            'categoria' => 'nullable',
            'company_id' => 'required',
        ]);
    }
}
