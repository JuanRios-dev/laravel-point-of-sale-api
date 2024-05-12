<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'numero',
        'fecha_vencimiento',
        'company_id'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function winery()
    {
        return $this->belongsToMany(Winery::class)->withPivot('cantidad', 'item_id');

    }
}
