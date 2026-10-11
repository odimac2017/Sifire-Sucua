<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformeDiarioFirmante extends Model
{
    /** @use HasFactory<\Database\Factories\InstitucionFactory> */
    use HasFactory;

    protected $table = 'informe_diario_firmantes';
    protected $fillable = [
        'informe_diario_id',
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
