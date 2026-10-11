<?php

namespace App\Models;

use App\Enums\AreaLocal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\NivelRiesgo;
use App\Enums\RecursoNecesario;
use App\Enums\Ubicacion;

class ProcesoPermisoTarifario extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory;

    protected $table = 'proceso_permiso_tarifarios';
    protected $fillable = [
        'proceso_permiso_id',
        'tarifario_id',
        'valor_tarifario',
        'valor_fijo',
        'calc_nivel_riesgo_item',
        'calc_nivel_riesgo_valor',
        'calc_local_item',
        'calc_local_valor',
        'calc_recursos_item',
        'calc_recursos_valor',
        'calc_ubicacion_item',
        'calc_ubicacion_valor',
        'cantidad',
        'subtotal'
    ];

    public function procesoPermiso()
    {
        return $this->belongsTo(ProcesoPermiso::class);
    }

    public function tarifario()
    {
        return $this->belongsTo(Tarifario::class);
    }    

    protected $casts = [
        'calc_nivel_riesgo_item' => NivelRiesgo::class,
        'calc_local_item' => AreaLocal::class,
        'calc_recursos_item' => RecursoNecesario::class,
        'calc_ubicacion_item' => Ubicacion::class,
    ];
}
