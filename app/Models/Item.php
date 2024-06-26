<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'imagen',
        'tipo',
        'iva_compra',
        'iva_venta',
        'precio',
        'categoria',
        'company_id',
    ];

    public function wineries()
    {
        return $this->belongsToMany(Winery::class)->withPivot('cantidad', 'fecha_vencimiento');
    }

    public function transfer_wineries()
    {
        return $this->belongsToMany(TransferWinery::class)->withPivot('cantidad', 'fecha_vencimiento');
    }
}
