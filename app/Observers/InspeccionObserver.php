<?php

namespace App\Observers;

use App\Jobs\GenerarPdfInspeccionJob;
use App\Models\Inspeccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspeccionObserver
{    
    public function created(Inspeccion $inspeccion): void
    {
        $inspeccion->solicitudInspeccion?->update([
            'estado' => 'REALIZADO'
        ]);

        if ($this->gestionarFirmantes($inspeccion)) {
            GenerarPdfInspeccionJob::dispatch($inspeccion->id)->afterCommit()
                ->onQueue('pdf');
        }        
    }
    
    public function updated(Inspeccion $inspeccion): void
    {
        if (
            $inspeccion->wasChanged('estado_firma') &&
            $inspeccion->estado_firma === 'PENDIENTE'
        ) {
            $archivo = $inspeccion->getOriginal('ruta_pdf_firmado');

            DB::transaction(function () use ($inspeccion) {
                $inspeccion->firmantes()
                    ->update([
                        'estado_firma' => 'PENDIENTE',
                        'fecha_firma' => null,
                    ]);

                $inspeccion->updateQuietly([
                    'ruta_pdf_firmado' => null,
                ]);
            });

            if (!empty($archivo)) {
                Storage::disk('private')->delete($archivo);
            }

            return;
        }
    }

    public function deleted(Inspeccion $inspeccion): void
    {
        $inspeccion->solicitudInspeccion?->update([
            'estado' => 'GENERADO'
        ]);

        $archivos = array_filter([
            $inspeccion->ruta_pdf,
            $inspeccion->ruta_pdf_firmado,
        ]);

        if (!empty($archivos)) {
            Storage::disk('private')->delete($archivos);
        }
    }

    private function gestionarFirmantes(Inspeccion $inspeccion): bool
    {
        $inspeccion->loadMissing('personal.firma');

        $firma = $inspeccion->personal->firma;

        if (!$firma || !$firma->ruta) {
            $inspeccion->updateQuietly([
                'estado_firma' => 'SIN_FIRMA',
            ]);

            return false;
        }

        // Evitar duplicados
        $existe = $inspeccion->firmantes()
            ->where('personal_id', $inspeccion->personal_id)
            ->exists();

        if (!$existe) {
            $inspeccion->firmantes()->create([
                'personal_id' => $inspeccion->personal_id,
                'orden_firma' => 1,
                'marcador_firma' => '__FIRMA_INSPECTOR__',
                'estado_firma' => 'PENDIENTE',
            ]);
        }

        return true;
    }    
}
