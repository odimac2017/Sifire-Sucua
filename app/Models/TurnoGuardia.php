<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoGuardia extends Model
{
    /** @use HasFactory<\Database\Factories\ParteEmergenciaFactory> */
    use HasFactory;

    protected $table = 'turnos_guardias';
    protected $fillable = [
        'personal_puesto_oficial_id',
        'nombre_oficial',
        'puesto_oficial',
        'nombre',
        'estado',
    ];

    public function personalPuesto()
    {
        return $this->belongsTo(PersonalPuesto::class, 'personal_puesto_oficial_id');
    }
}
