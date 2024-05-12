<?php

namespace App\Validations;

use Illuminate\Support\Facades\Validator;

class ProviderValidation
{
    public static function store($request)
    {
        $request->validate([
            'tipo_documento' => 'required|in:CC,TI,CE,NIT,PB',
            'numero_documento' => 'required|documento_unico:' . $request->input('company_id') . ',Provider',
            'nombre_razonsocial' => 'required',
            'telefono' => 'required|unique:providers,telefono,NULL,id,company_id,' . $request->input('company_id'),
            'correo' => 'required|email|unique:providers,correo,NULL,id,company_id,' . $request->input('company_id'),
            'direccion' => 'nullable',
            'municipio' => 'nullable',
            'responsable_iva' => 'nullable',
            'company_id' => 'required'
        ]);
    }

    public static function update($request, $provider)
    {
        $request->validate([
            'tipo_documento' => 'required|in:CC,TI,CE,NIT,PB',
            'numero_documento' => 'required|documento_unico:' . $request->input('company_id') . ',Provider' . ',' . $provider->id,
            'nombre_razonsocial' => 'required',
            'telefono' => 'required|unique:providers,telefono,' . $provider->id . ',id,company_id,' . $request->input('company_id'),
            'correo' => 'required|email|unique:providers,correo,' . $provider->id . ',id,company_id,' . $request->input('company_id'),
            'direccion' => 'nullable',
            'municipio' => 'nullable',
            'responsable_iva' => 'nullable',
            'company_id' => 'required'
        ]);
    }    
}
