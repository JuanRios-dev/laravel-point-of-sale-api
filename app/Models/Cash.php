<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cash extends Model
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
        'estado'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
