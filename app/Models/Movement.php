<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'winery_id',
        'fecha',
        'tipo',
        'detalles',
        'total',
        'company_id'
    ];

    public function winery()
    {
        return $this->belongsTo(Winery::class, 'winery_id');
    }


    public function items()
    {
        return $this->belongsToMany(Item::class)
            ->withPivot('cantidad', 'costo_unitario', 'costo_total', 'lot_id');
    }
}
