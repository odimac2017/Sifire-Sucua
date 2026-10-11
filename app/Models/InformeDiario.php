<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformeDiario extends Model
{
    /** @use HasFactory<\Database\Factories\EstadoCivilFactory> */
    use HasFactory;

    protected $table = 'informes_diarios';
    protected $fillable = [
        'plantilla_id',
        'personal_id',
        'personal_puesto_id',
        'personal_puesto_dirige_id',
        'nombre_personal',
        'puesto_personal',
        'nombre_dirige',
        'puesto_dirige',
        'numero',
        'secuencia',
        'fecha_informe',
        'contenido',
        'inspecciones_aprobadas',
        'inspecciones_reinspeccion',
        'inspecciones_no_realizadas',
        'inspecciones_vehiculares',
        'notificaciones_realizadas',
        'total_general',
        'observaciones',
        'despedida',
        'estado',
        'estado_firma',
        'ruta_pdf',
        'ruta_pdf_firmado'
    ];

    public function personalPuestoDirige(){
        return $this->belongsTo(PersonalPuesto::class, 'personal_puesto_dirige_id');
    }

    public function firmantes(){
        return $this->hasMany(InformeDiarioFirmante::class);
    }
}
