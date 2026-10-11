<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteEmergenciaPersonal extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'parte_emergencia_personal';
    protected $fillable = [
        'parte_emergencia_id',
        'personal_id',
        'nombre_personal',
    ];
}
