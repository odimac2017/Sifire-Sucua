<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalculoPermiso extends Model
{
    /** @use HasFactory<\Database\Factories\ConfiguracionFactory> */
    use HasFactory;

    protected $table = 'calculos_permisos';
    protected $fillable = [        
        'periodo',
        'calc_niv_riesg_alto',
        'calc_niv_riesg_medio',
        'calc_niv_riesg_bajo',
        'calc_local_grande',
        'calc_local_media',
        'calc_local_pequeno',
        'calc_local_minimo',
        'calc_rec_necesarios_especial',
        'calc_rec_necesarios_medio',
        'calc_rec_necesarios_minimo',
        'calc_ubicacion_canton',
        'calc_ubicacion_parroquia',
        'calc_ubicacion_rural',
        'calc_reinspeccion'
    ];
}
