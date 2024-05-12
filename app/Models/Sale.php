<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'prefijo',
        'numero',
        'fecha',
        'formaPago',
        'subTotal',
        'totalImpuestos',
        'total',
        'descuento',
        'valorDescuento',
        'cash_id',
        'observaciones',
        'company_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_sale')
            ->withPivot('cantidad', 'precio_unitario', 'descuento', 'valor_descuento', 'subtotal', 'impuestos', 'precio_total', 'lot_id')
            ->withTimestamps();
    }
}
