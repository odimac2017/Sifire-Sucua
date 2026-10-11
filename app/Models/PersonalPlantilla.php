<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalPlantilla extends Model
{
    /** @use HasFactory<\Database\Factories\PersonalFactory> */
    use HasFactory;

    protected $table = 'personal_plantillas';
    protected $fillable = [
        'personal_id',
        'plantilla_id',
        'puesto',
        'orden_firma',
        'marcador_firma',
        'firma_electronica',
    ];

    public function firmante(){
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function plantilla(){
        return $this->belongsTo(Plantilla::class);
    }
}
