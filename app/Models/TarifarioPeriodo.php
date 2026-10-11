<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifarioPeriodo extends Model
{
    /** @use HasFactory<\Database\Factories\TarifarioFactory> */
    use HasFactory;
    
    protected $table = 'tarifario_periodos';

    protected $fillable = [
        'tarifario_id',
        'periodo',
        'valor',
        'valor_fijo',
    ];   
    
    protected $casts = [
        'valor' => 'float',
        'riesgo' => 'float',
        'valor_fijo' => 'float',
    ];
}
