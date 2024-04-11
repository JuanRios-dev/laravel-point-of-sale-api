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
        'company_id'
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('cantidad', 'fecha_vencimiento');
    }
}
