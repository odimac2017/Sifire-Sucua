<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspeccion extends Model
{
    /** @use HasFactory<\Database\Factories\InspeccionFactory> */
    use HasFactory;

    protected $table = 'inspecciones';
    protected $fillable = [
        'plantilla_id',
        'proceso_permiso_id',
        'solicitud_inspeccion_id',
        'personal_id',
        'nombre_personal',
        'numero',
        'fecha_inspeccion',
        'tipo_inspeccion',
        'e_tipo_funcion',
        'e_tipo_funcion_otros',
        'e_materiales_almacena',
        'e_rgo_estr_hormigon',
        'e_rgo_estr_madera',
        'e_rgo_estr_mixta',
        'e_rgo_estr_metalica',
        'e_rgo_estr_otros',
        'e_rgo_estr_cub_metalica',
        'e_rgo_estr_cub_madera',
        'e_rgo_estr_cub_tumbado',
        'e_rgo_estr_cub_fibrocemento',
        'e_rgo_estr_cub_playwood',
        'e_rgo_estr_cub_otros',
        'e_rgo_estr_num_pisos',
        'e_rgo_estr_piso_ocupa',
        'e_rgo_estr_area_local',
        'e_rgo_estr_surt_diesel',
        'e_rgo_estr_surt_gasolina',
        'e_rgo_estr_cap_diesel',
        'e_rgo_estr_cap_gasolina',
        'e_rgo_estr_cap_gas',
        'e_rgo_estr_trat_ingnifugo',
        'e_rgo_estr_piso_antideslisante',
        'e_rgo_estr_piso_deslisante',
        'e_rgo_estr_piso_otro',
        'e_rgo_estr_norma_inen',
        'e_rgo_estr_observaciones',
        'e_rgo_elect_buenas',
        'e_rgo_elect_defectuosas',
        'e_rgo_elect_malas',
        'e_rgo_elect_empotradas',
        'e_rgo_elect_sobrepuestas',
        'e_rgo_elect_volt_110',
        'e_rgo_elect_volt_220',
        'e_rgo_elect_volt_industrial',
        'e_rgo_elect_protec_electrica',
        'e_rgo_elect_transformadores',
        'e_rgo_elect_trans_kva',
        'e_rgo_elect_generadores',
        'e_rgo_elect_electricos',
        'e_rgo_elect_combustible',
        'e_rgo_salud_epp_casco',
        'e_rgo_salud_epp_gafas',
        'e_rgo_salud_epp_calzado',
        'e_rgo_salud_epp_vestimenta',
        'e_rgo_salud_epp_guantes',
        'e_rgo_salud_epp_orejeras',
        'e_rgo_salud_observaciones',
        'e_sis_incend_pqs',
        'e_sis_incend_co2',
        'e_sis_incend_h2o',
        'e_sis_incend_capacidad',
        'e_sis_incend_det_humo',
        'e_sis_incend_lamp_emer',
        'e_sis_incend_gabinete',
        'e_sis_incend_sprinklers',
        'e_sis_incend_alarm_incendio',
        'e_sis_incend_bomb_hidraulica',
        'e_sis_incend_siamesa',
        'e_sis_incend_escalera_emer',
        'e_sis_incend_puert_corta_fuego',
        'e_sis_incend_senal_emer',
        'e_sis_incend_puert_emer',
        'e_sis_incend_tanq_reserv_agua',
        'e_sis_incend_cap_tanq_reserv',
        'v_tipo_combustible',
        'v_cantidad_combustible',
        'v_cantidad_cilindros',
        'v_origen_transporte',
        'v_destino_transporte',
        'v_tipo_extintor',
        'v_capacidad_extintor',
        'v_triangulos_seguridad',
        'v_materiales_peligrosos',
        'v_control_derrames',
        'v_descarga_tierra',
        'v_arrestallamas',
        'v_senalizacion',
        'v_botiquin',
        'v_luces_stop',
        'v_luces_parking',
        'medidas_obligatorias',
        'extintores_observaciones',
        'artesano_calificado',
        'porcentaje_bomberos',
        'valor_permiso',
        'valor_inspeccion',
        'porcentaje_descuento',
        'valor_descuento',
        'subtotal',
        'valor_total',
        'estado',
        'procesado',
        'plazo_permiso',
        'motivo_nuevo_plazo',
        'estado_firma',
        'ruta_pdf',
        'ruta_pdf_firmado',
        'correo_enviado_at',
        'correo_destinatario',
        'correo_estado',
        'correo_error',
        'codigo_verificacion',
    ];

    protected $casts = [
        'fecha_inspeccion' => 'datetime',
        'plazo_permiso' => 'date',
        'fecha_firma' => 'datetime',        
    ];

    public function procesoPermiso(){
        return $this->belongsTo(ProcesoPermiso::class);
    }

    public function solicitudInspeccion(){
        return $this->belongsTo(SolicitudInspeccion::class);
    }

    public function personal(){
        return $this->belongsTo(Personal::class);
    }

    public function firmantes(){
        return $this->hasMany(InspeccionFirmante::class);
    }

    public function archivos(){
        return $this->hasMany(InspeccionArchivo::class);
    }

    protected $appends = ['numero_formateado'];

    public function getNumeroFormateadoAttribute(){
        return str_pad($this->numero, 7, '0', STR_PAD_LEFT);
    }

    public function getFechaAttribute(){
        return $this->fecha_inspeccion;
    }
}
