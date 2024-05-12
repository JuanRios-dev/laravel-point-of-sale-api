<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'codigo',
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

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_provider_invoice')
            ->withPivot('cantidad', 'precio_unitario', 'descuento', 'valor_descuento', 'subtotal', 'impuestos', 'precio_total', 'lot_id')
            ->withTimestamps();
    }
}
