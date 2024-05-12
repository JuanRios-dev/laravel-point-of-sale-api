<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'fecha_apertura',
        'fecha_cierre',
        'saldo_apertura',
        'saldo_cierre',
        'user_apertura_id',
        'user_cierre_id',
    ];

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }
}
