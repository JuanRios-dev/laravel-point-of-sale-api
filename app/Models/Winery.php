<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winery extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'nombre',
        'descripcion',
        'ubicacion'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('cantidad', 'fecha_vencimiento');
    }

    public function movements()
    {
        return $this->belongsToMany(Movement::class)->withPivot('cantidad', 'fecha_vencimiento');
    }
}
