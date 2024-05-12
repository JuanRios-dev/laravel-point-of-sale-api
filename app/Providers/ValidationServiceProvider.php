<?php

namespace App\Providers;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        Validator::extend('documento_unico', function ($attribute, $value, $parameters, $validator) {
            $company_id = $parameters[0];
            $tipo_documento = $validator->getData()['tipo_documento'];
            $model_name = $parameters[1];
            $customer_id = isset($parameters[2]) ? $parameters[2] : null;

            $model = app("App\Models\\$model_name");

            $existingCustomer = $model::where('tipo_documento', $tipo_documento)
                ->where('company_id', $company_id)
                ->where('numero_documento', $value)
                ->where('id', '!=', $customer_id)
                ->first();

            if ($customer_id) {
                if ($tipo_documento === 'NIT') {
                    $existingCustomer = $model::where('company_id', $company_id)
                        ->where('numero_documento', $value)
                        ->where('tipo_documento', '=', 'NIT')
                        ->where('id', '!=', $customer_id)
                        ->get();

                    if (!$existingCustomer->isEmpty()) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    $existingCustomer = $model::where('company_id', $company_id)
                        ->where('numero_documento', $value)
                        ->where('tipo_documento', '!=', 'NIT')
                        ->where('id', '!=', $customer_id)
                        ->get();

                    if (!$existingCustomer->isEmpty()) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }

            if (!$existingCustomer) {
                if ($tipo_documento === 'NIT') {
                    return true;
                } else {
                    $existingCustomer = $model::where('company_id', $company_id)
                        ->where('numero_documento', $value)
                        ->where('tipo_documento', $tipo_documento)
                        ->get();

                    if (!$existingCustomer->isEmpty()) {
                        foreach ($existingCustomer as $customer) {
                            if ($customer->tipo_documento === $tipo_documento) {
                                return false;
                            }
                        }
                    } else {
                        return true;
                    }
                }
            } else {
                return false;
            }
        });

        Validator::replacer('documento_unico', function ($attribute) {
            return str_replace(':attribute', $attribute, 'El Documento ya ha sido registrado.');
        });
    }
}
