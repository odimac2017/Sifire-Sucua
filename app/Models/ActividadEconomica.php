<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadEconomica extends Model
{
    /** @use HasFactory<\Database\Factories\ActividadEconomicaFactory> */
    use HasFactory;

    protected $table = 'actividades_economicas';
    protected $fillable = [
        'cliente_id',
        'provincia_id',
        'canton_id',
        'parroquia_id',
        'tipo_actividad',
        'e_nombre_comercial',
        'e_actividad_principal',
        'e_actividad_secundaria',
        'e_clave_catastral',
        'e_sector',
        'e_direccion',
        'e_telefonos',
        'e_latitud',
        'e_longitud',
        'e_ruta_mapa',
        'v_compania_id',
        'v_placa',
        'v_marca',
        'v_clase',
        'v_color',
        'v_tipo',
        'v_cilindraje',
        'v_combustible',
        'v_numero_unidad',
        'fecha_inicio',
        'estado'
    ];

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function compania(){
        return $this->belongsTo(Cliente::class, 'v_compania_id', 'id');
    }

    public function provincia(){
        return $this->belongsTo(Provincia::class);
    }

    public function canton(){
        return $this->belongsTo(Canton::class);
    }

    public function parroquia(){
        return $this->belongsTo(Parroquia::class);
    }
}
