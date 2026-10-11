<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClasificacionBien extends Model
{
    /** @use HasFactory<\Database\Factories\EstadoCivilFactory> */
    use HasFactory;

    protected $table = 'clasificaciones_bienes';
    protected $fillable = [
        'nombre',
        'cuenta',
        'estado'
    ];

    public function bienes(){
        return $this->hasMany(Bien::class);
    }
}
