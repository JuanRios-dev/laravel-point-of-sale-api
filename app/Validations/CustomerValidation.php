<?php

namespace App\Validations;

class CustomerValidation
{
    public static function store($request)
    {
        $request->validate([
            'tipo_documento' => 'required|in:CC,TI,CE,NIT,PB',
            'numero_documento' => 'required|documento_unico:' . $request->input('company_id') . ',Customer',
            'nombre_razonsocial' => 'required',
            'telefono' => 'required|unique:customers,telefono,NULL,id,company_id,' . $request->input('company_id'),
            'correo' => 'nullable|email|unique:customers,correo,NULL,id,company_id,' . $request->input('company_id'),
            'direccion' => 'nullable',
            'municipio' => 'nullable',
            'company_id' => 'required'
        ], [
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser CC, TI, CE, NIT o PB.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.documento_unico' => 'El número de documento ya ha sido registrado.',
            'nombre_razonsocial.required' => 'El nombre o razón social es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.unique' => 'El teléfono ya ha sido registrado.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico debe ser válido.',
            'correo.unique' => 'El correo electrónico ya ha sido registrado.',
            'company_id.required' => 'El ID de la compañía es obligatorio.'
        ]);
    }

    public static function update($request, $customer)
    {
        $request->validate([
            'tipo_documento' => 'required',
            'numero_documento' => 'required|documento_unico:' . $request->input('company_id') . ',Customer' . ',' . $customer->id,
            'nombre_razonsocial' => 'required',
            'telefono' => 'required|unique:customers,telefono,' . $customer->id . ',id,company_id,' . $request->input('company_id'),
            'correo' => 'nullable|email|unique:customers,correo,' . $customer->id . ',id,company_id,' . $request->input('company_id'),
            'direccion' => 'nullable',
            'municipio' => 'nullable',
            'company_id' => 'required'
        ], [
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser CC, TI, CE, NIT o PB.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.documento_unico' => 'El número de documento ya ha sido registrado.',
            'nombre_razonsocial.required' => 'El nombre o razón social es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.unique' => 'El teléfono ya ha sido registrado.',
            'correo.email' => 'El correo electrónico debe ser válido.',
            'correo.unique' => 'El correo electrónico ya ha sido registrado.',
            'company_id.required' => 'El ID de la compañía es obligatorio.'
        ]);
    }    
}
