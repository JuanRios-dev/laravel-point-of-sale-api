<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_id',
        'tipo',
        'monto',
        'detalles',
    ];

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }
}
