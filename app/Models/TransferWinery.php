<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferWinery extends Model
{
    use HasFactory;

    protected $fillable = [
        'winery_origen_id',
        'winery_destino_id',
        'fecha',
        'detalles',
        'company_id'
    ];

    public function transferItems()
    {
        return $this->belongsToMany(Item::class)->withPivot('cantidad', 'lot_id');
    }

    public function origen()
    {
        return $this->belongsTo(Winery::class, 'winery_origen_id');
    }

    public function destino()
    {
        return $this->belongsTo(Winery::class, 'winery_destino_id');
    }
}
