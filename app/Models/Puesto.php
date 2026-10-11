<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    /** @use HasFactory<\Database\Factories\PuestoFactory> */
    use HasFactory;

    protected $fillable = [
        'area_id',
        'regimen_id',
        'modalidad_id',
        'escala_id',
        'nombre',
        'jefe_area',
        'autoridad_nominadora',
        'responsable_tthh',
        'total_vacaciones',
        'partida_individual',
        'lugar_trabajo',
        'observaciones',
        'estado'
    ];
}
