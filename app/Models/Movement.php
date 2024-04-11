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
        'company_id'
    ];

    public function wineries()
    {
        return $this->belongsToMany(Winery::class)->withPivot('cantidad', 'fecha_vencimiento');
    }
}
