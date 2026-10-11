<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteEmergencia extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'partes_emergencias';
    protected $fillable = [
        'plantilla_id',
        'provincia_id',
        'canton_id',
        'parroquia_id',
        'personal_id',
        'personal_puesto_id',
        'personal_puesto_revisa_id',
        'personal_radio_id',
        'nombre_personal',
        'puesto_personal',
        'nombre_revisa',
        'puesto_revisa',
        'nombre_radio',
        'numero',
        'secuencia',
        'inicial',
        'seguimiento',
        'final',
        'fecha_llamada',
        'fecha_llegada',
        'fecha_atencion',
        'direccion',
        'fuente',
        'contexto_general',
        'personal_externo',
        'ver_geolocalizacion',
        'latitud',
        'longitud',
        'estado',
        'ruta_mapa',
        'estado_firma',
        'ruta_pdf',
        'ruta_pdf_firmado',
    ];

    protected $casts = [
        'fecha_llamada' => 'datetime',
        'fecha_llegada' => 'datetime',
        'fecha_atencion' => 'datetime',
    ];

    public function categorias(){
        return $this->hasMany(ParteEmergenciaCategoria::class);
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

    public function personal(){
        return $this->belongsTo(Personal::class);
    }

    public function personales(){
        return $this->belongsToMany(Personal::class, 'parte_emergencia_personal', 'parte_emergencia_id', 'personal_id')->withPivot('nombre_personal');
    }

    public function personalesQap(){
        return $this->belongsToMany(Personal::class, 'parte_emergencia_personal_qap', 'parte_emergencia_id', 'personal_id')->withPivot('nombre_personal','desde','hasta','tiempo_total');
    }

    public function insumos(){
        return $this->belongsToMany(Insumo::class, 'parte_emergencia_insumos', 'parte_emergencia_id', 'insumo_id')->withPivot('cantidad');
    }

    public function archivos(){
        return $this->hasMany(ParteEmergenciaArchivo::class);
    }

    public function fotos(){
        return $this->hasMany(ParteEmergenciaArchivo::class)
            ->where('tipo', 'imagen');
    }

    public function firmantes(){
        return $this->hasMany(ParteEmergenciaFirmante::class);
    }

    protected $appends = ['numero_formateado'];

    public function getNumeroFormateadoAttribute(){
        return str_pad($this->numero, 7, '0', STR_PAD_LEFT);
    }
}
