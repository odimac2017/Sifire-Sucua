<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteConsignaVehiculo extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'parte_consigna_vehiculos';
    protected $fillable = [
        'parte_consigna_id',
        'bien_id',
        'operativo',
        'motivo',
    ];

    public function bien(){
        return $this->belongsTo(Bien::class);
    }
}
