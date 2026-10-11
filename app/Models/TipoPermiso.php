<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPermiso extends Model
{
    /** @use HasFactory<\Database\Factories\TipoClienteFactory> */
    use HasFactory;

    protected $table = 'tipos_permisos';
    protected $fillable = [
        'plantilla_id',
        'tipo_inspeccion_id',
        'nombre',
        'estado'
    ];

    public function tipoInspeccion(){
        return $this->belongsTo(TipoInspeccion::class);
    }

    public function plantilla(){
        return $this->belongsTo(Plantilla::class);
    }
}
