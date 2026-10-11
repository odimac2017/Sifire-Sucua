<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcesoPermiso extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory;

    protected $table = 'procesos_permisos';
    protected $fillable = [
        'cliente_id', 
        'actividad_economica_id',
        'tipo_permiso_id', 
        'permiso_funcionamiento_id',
        'artesano_calificado',
        'porcentaje_descuento',
        'porcentaje_bomberos',
        'valor_impuesto_predial',
        'motivo_cancela', 
        'finished_at'
    ];

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function actividadEconomica(){
        return $this->belongsTo(ActividadEconomica::class);
    }

    public function tipoPermiso(){
        return $this->belongsTo(TipoPermiso::class);
    }

    public function tarifarios(){
        return $this->hasMany(ProcesoPermisoTarifario::class);
    }    

    public function solicitudesInspecciones(){
        return $this->hasMany(SolicitudInspeccion::class);
    }

    public function solicitudInspeccion(){
        return $this->hasOne(SolicitudInspeccion::class);
    }

    public function inspeccion(){
        return $this->hasOne(Inspeccion::class);
    }

    public function inspeccionAprobada(){
        return $this->hasOne(Inspeccion::class)->where('estado', 'APROBADO');
    }

    public function inspecciones()
    {
        return $this->hasMany(Inspeccion::class);
    }

    public function notificaciones(){
        return $this->hasMany(Notificacion::class);
    }

    public function extintores(){
        return $this->hasMany(Extintor::class);
    }

    public function requisitos(){
        return $this->hasMany(ProcesoPermisoRequisito::class);
    }

    public function ultimaInspeccion(){
        return $this->hasOne(Inspeccion::class)
            ->latest('fecha_inspeccion');
    }

    public function inspeccionInicial(){
        return $this->hasOne(Inspeccion::class)
            ->whereHas('solicitudInspeccion', function ($q) {
                $q->where('tipo_solicitud', 'IN');
            });
    }

    public function reinspecciones(){
        return $this->hasMany(Inspeccion::class)
            ->whereHas('solicitudInspeccion', function ($q) {
                $q->where('tipo_solicitud', 'RE');
            });
    }
}
