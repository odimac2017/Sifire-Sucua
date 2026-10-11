<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteEmergenciaCategoria extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'parte_emergencia_categorias';
    protected $fillable = [
        'parte_emergencia_id',
        'categoria_emergencia_id',
        'categoria1',
        'categoria2',
    ];
}
