<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteConsignaReporteQap extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'parte_consigna_reportes_qap';
    protected $fillable = [
        'parte_consigna_id',
        'personal_id',
        'nombre_personal',
        'hora',
        'reporte',
    ];
}
