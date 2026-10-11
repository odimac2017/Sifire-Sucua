<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory;

    protected $fillable = [
        'documento_id', 
        'provincia_id',
        'canton_id', 
        'parroquia_id', 
        'tipo_cliente_id',
        'documento', 
        'nombre',
        'compania',
        'artesano_calificado',
        'documento_representante_legal',
        'nombre_representante_legal',
        'direccion',
        'telefonos',
        'email',
        'porcentaje_descuento',
        'archivo_fisico',
        'estado'
    ];

    public function parroquia(){
        return $this->belongsTo(Parroquia::class);
    }

    public function actividadesEconomicas(){
        return $this->hasMany(ActividadEconomica::class);
    }
}
