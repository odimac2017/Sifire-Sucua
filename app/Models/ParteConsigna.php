<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteConsigna extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'partes_consignas';
    protected $fillable = [
        'plantilla_id',
        'turno_guardia_id',
        'turno_guardia_recibe_id',
        'personal_entrega_id',
        'personal_recibe_id',
        'personal_puesto_revisa_id',
        'nombre_entrega',
        'puesto_entrega',
        'nombre_recibe',
        'puesto_recibe',
        'nombre_revisa',
        'puesto_revisa',
        'fecha',
        'novedades',
        'estado',
    ];

    public function turnoGuardia(){
        return $this->belongsTo(TurnoGuardia::class);
    }

    public function integrantes(){
        return $this->hasMany(ParteConsignaIntegrante::class);
    }

    public function vehiculos(){
        return $this->hasMany(ParteConsignaVehiculo::class);
    }

    public function consignas(){
        return $this->hasMany(ParteConsignaConsigna::class);
    }

    public function reportesQap(){
        return $this->belongsToMany(Personal::class, 'parte_consigna_reportes_qap', 'parte_consigna_id', 'personal_id')->withPivot('id','nombre_personal','hora','reporte');
    }

    public function planificaciones(){
        return $this->belongsToMany(Personal::class, 'parte_consigna_planificaciones', 'parte_consigna_id', 'personal_id')->withPivot('id','nombre_personal','actividad','cumple','motivo');
    }
}
