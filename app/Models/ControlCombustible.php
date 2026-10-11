<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlCombustible extends Model
{
    /** @use HasFactory<\Database\Factories\EstadoCivilFactory> */
    use HasFactory;

    protected $table = 'controles_combustibles';
    protected $fillable = [
        'bien_id',
        'personal_id',
        'combustible_id',
        'nombre_chofer',
        'numero',
        'fecha',
        'km_recorrido',
        'cantidad_galones',
        'precio_galon',
        'subtotal',
        'porcentaje_iva',
        'valor_iva',
        'valor_total',
        'estado'
    ];

    public function bien(){
        return $this->belongsTo(Bien::class);
    }

    public function combustible(){
        return $this->belongsTo(Combustible::class);
    }

    protected $appends = ['numero_formateado'];

    public function getNumeroFormateadoAttribute(){
        return str_pad($this->numero, 7, '0', STR_PAD_LEFT);
    }
}
