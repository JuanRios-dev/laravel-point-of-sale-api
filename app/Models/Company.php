<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'nit',
        'nombre',
        'dieccion',
        'telefono',
        'correo',
        'pais',
        'moneda',
        'codigo_postal'
    ];
    
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function wineries()
    {
        return $this->hasMany(Winery::class);
    }

    public function cashes()
    {
        return $this->hasMany(Cash::class);
    }
}
