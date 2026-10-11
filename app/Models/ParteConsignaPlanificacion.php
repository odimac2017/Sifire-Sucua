<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteConsignaPlanificacion extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'parte_consigna_planificaciones';
    protected $fillable = [
        'parte_consigna_id',
        'personal_id',
        'nombre_personal',
        'actividad',
        'cumple',
        'motivo',
    ];
}
