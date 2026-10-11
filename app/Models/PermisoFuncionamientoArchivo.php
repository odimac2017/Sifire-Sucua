<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisoFuncionamientoArchivo extends Model
{
    /** @use HasFactory<\Database\Factories\InstitucionFactory> */
    use HasFactory;

    protected $table = 'permiso_funcionamiento_archivos';
    protected $fillable = [
        'permiso_funcionamiento_id',
        'nombre',
        'ruta',
        'tipo',
        'size',
    ];

    public function permisoFuncionamiento(){
        return $this->belongsTo(PermisoFuncionamiento::class);
    }
}
