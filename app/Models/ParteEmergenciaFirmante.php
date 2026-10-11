<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParteEmergenciaFirmante extends Model
{
    /** @use HasFactory<\Database\Factories\InstitucionFactory> */
    use HasFactory;

    protected $table = 'parte_emergencia_firmantes';
    protected $fillable = [
        'parte_emergencia_id',
        'personal_id',
        'orden_firma',
        'marcador_firma',
        'estado_firma',
        'fecha_firma',
        'error_firma',
    ];

    public function personal()
    {
        return $this->belongsTo(Personal::class);
    }
}
