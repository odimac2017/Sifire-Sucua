<?php

namespace App\Observers;

use App\Jobs\GenerarPdfPermisoFuncionamientoJob;
use App\Models\PermisoFuncionamiento;
use App\Models\PersonalPlantilla;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PermisoFuncionamientoObserver
{
    public function created(PermisoFuncionamiento $permisoFuncionamiento): void
    {
        \App\Models\ProcesoPermiso::whereNull('finished_at')
        ->where('actividad_economica_id', $permisoFuncionamiento->procesoPermiso->actividad_economica_id)
        ->update([
            'permiso_funcionamiento_id' => $permisoFuncionamiento->id,
            'finished_at' => now(),
        ]);
       
        if ($this->gestionarFirmantes($permisoFuncionamiento)) {
            GenerarPdfPermisoFuncionamientoJob::dispatch($permisoFuncionamiento->id)
                ->onQueue('pdf');
        }
    }

    public function updated(PermisoFuncionamiento $permisoFuncionamiento): void
    {
        // =========================
        // CAMBIO A NULL FINISHED_AT DE PROCESOS_PERMISOS
        // =========================
        if ($permisoFuncionamiento->wasChanged('estado') && $permisoFuncionamiento->estado === 'ANULADO') {
            
            $permisoFuncionamiento->procesoPermiso->update([
                'finished_at' => null
            ]);

            if ($permisoFuncionamiento->estado_firma != 'SIN_FIRMA'){
                if ($permisoFuncionamiento->ruta_pdf_firmado && Storage::exists($permisoFuncionamiento->ruta_pdf_firmado)) {
                    Storage::disk('private')->delete($permisoFuncionamiento->ruta_pdf_firmado);
                }
                
                $permisoFuncionamiento->update([
                    'estado_firma' => 'ANULADO',
                    'ruta_pdf_firmado' => null
                ]);

                // opcional: detener firmas pendientes
                $permisoFuncionamiento->firmantes()
                    ->update(['estado_firma' => 'ANULADO']);
            }
        }

        if (
            $permisoFuncionamiento->wasChanged('estado_firma') &&
            $permisoFuncionamiento->estado_firma === 'PENDIENTE'
        ) {
            $archivo = $permisoFuncionamiento->getOriginal('ruta_pdf_firmado');

            DB::transaction(function () use ($permisoFuncionamiento) {
                $permisoFuncionamiento->firmantes()                    
                    ->update([
                        'estado_firma' => 'PENDIENTE',
                        'fecha_firma' => null,
                    ]);

                $permisoFuncionamiento->updateQuietly([
                    'ruta_pdf_firmado' => null,
                ]);
            });

            if (!empty($archivo)) {
                Storage::disk('private')->delete($archivo);
            }

            return;
        } 
    }

    public function deleted(PermisoFuncionamiento $permisoFuncionamiento): void
    {
        $archivos = array_filter([
            $permisoFuncionamiento->ruta_pdf,
            $permisoFuncionamiento->ruta_pdf_firmado,
        ]);

        if (!empty($archivos)) {
            Storage::disk('private')->delete($archivos);
        }
    }

    private function gestionarFirmantes(PermisoFuncionamiento $permisoFuncionamiento): bool
    {
        $firmantes = PersonalPlantilla::where(
            'plantilla_id',
            $permisoFuncionamiento->plantilla_id
        )->get();

        if ($firmantes->isEmpty()) {
            $permisoFuncionamiento->updateQuietly([
                'estado_firma' => 'SIN_FIRMA',
            ]);

            return false;
        }

        $sinFirmaElectronica = $firmantes->contains(
            fn ($f) => (int) $f->firma_electronica === 0
        );

        if ($sinFirmaElectronica) {
            $permisoFuncionamiento->updateQuietly([
                'estado_firma' => 'SIN_FIRMA',
            ]);

            return false;
        }

        if (!$permisoFuncionamiento->firmantes()->exists()) {
            $permisoFuncionamiento->firmantes()->createMany(
                $firmantes->map(fn ($f) => [
                    'personal_id' => $f->personal_id,
                    'orden_firma' => $f->orden_firma,
                    'marcador_firma' => $f->marcador_firma,
                    'estado_firma' => 'PENDIENTE',
                ])->toArray()
            );
        }

        return true;
    }
}
