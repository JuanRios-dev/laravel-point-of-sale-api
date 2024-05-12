<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SearchCollection extends Collection
{
    public static function searchGeneric(Model $model, $search, $companyId)
    {
        $columns = $model->getFillable();

        return $model::query()->where(function ($query) use ($columns, $search, $companyId) {
            foreach ($columns as $field) {
                $query->orWhere($field, 'LIKE', '%' . $search . '%');
            }
        })->where('company_id', $companyId);
    }
}
