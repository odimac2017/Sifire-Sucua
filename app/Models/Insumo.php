<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    /** @use HasFactory<\Database\Factories\EstadoCivilFactory> */
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'presentacion',
        'estado'
    ];

    public function partesEmergenciasInsumos(){
        return $this->hasMany(ParteEmergenciaInsumo::class);
    }
}
