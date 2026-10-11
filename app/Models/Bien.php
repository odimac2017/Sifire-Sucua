<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bien extends Model
{
    /** @use HasFactory<\Database\Factories\CantonFactory> */
    use HasFactory;

    protected $table = 'bienes';
    
    protected $fillable = [
        'clasificacion_bien_id',
        'codigo_inventario',
        'tipo_bien',
        'marca',
        'modelo',
        'serie',
        'identificacion',
        'placa',
        'numero_chasis',
        'numero_motor',
        'cilindraje',
        'anio_modelo',
        'clase_vehiculo',
        'tipo_vehiculo',
        'pais_origen',
        'combustible',
        'color1',
        'color2',
        'caracteristicas',
        'fecha_ingreso',
        'vida_util',
        'interviene_emergencia',
        'control_combustible',
        'observaciones',
        'fecha_baja',
        'explicacion_baja',
        'estado',
        'ruta_foto'
    ];

    public function clasificacionBien(){
        return $this->belongsTo(ClasificacionBien::class);
    }
}
