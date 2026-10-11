<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteEmergenciaInsumo extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'parte_emergencia_insumos';
    protected $fillable = [
        'parte_emergencia_id',
        'insumo_id',
        'cantidad',
    ];
}
