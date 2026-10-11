<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteConsignaIntegrante extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'parte_consigna_integrantes';
    protected $fillable = [
        'parte_consigna_id',
        'funcion_consigna_id',
        'personal_id',
        'nombre_personal',
        'nombre_funcion',
    ];
}
