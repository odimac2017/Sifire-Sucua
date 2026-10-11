<?php

namespace App\Observers;

use App\Jobs\GenerarPdfParteEmergenciaJob;
use App\Models\ParteEmergencia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ParteEmergenciaObserver
{
    public function created(ParteEmergencia $parteEmergencia): void
    {        
        if ($this->gestionarFirmantes($parteEmergencia)) {
            GenerarPdfParteEmergenciaJob::dispatch($parteEmergencia->id)
                ->onQueue('pdf');
        }
    }

    public function updated(ParteEmergencia $parteEmergencia): void
    {        
        if (
            $parteEmergencia->wasChanged('estado_firma') &&
            $parteEmergencia->estado_firma === 'PENDIENTE'
        ) {
            $archivo = $parteEmergencia->getOriginal('ruta_pdf_firmado');

            DB::transaction(function () use ($parteEmergencia) {
                $parteEmergencia->firmantes()
                    ->update([
                        'estado_firma' => 'PENDIENTE',
                        'fecha_firma' => null,
                    ]);

                $parteEmergencia->updateQuietly([
                    'ruta_pdf_firmado' => null,
                ]);
            });

            if (!empty($archivo)) {
                Storage::disk('private')->delete($archivo);
            }

            return;
        }
    }

    public function deleted(ParteEmergencia $parteEmergencia): void
    {
        $archivos = array_filter([
            $parteEmergencia->ruta_pdf,
            $parteEmergencia->ruta_pdf_firmado
        ]);

        if (!empty($archivos)) {
            Storage::disk('private')->delete($archivos);
        }
    }

    private function gestionarFirmantes(ParteEmergencia $parteEmergencia): bool
    {
        $parteEmergencia->loadMissing('personal.firma');

        $firma = $parteEmergencia->personal->firma;

        if (!$firma || !$firma->ruta) {
            $parteEmergencia->updateQuietly([
                'estado_firma' => 'SIN_FIRMA',
            ]);

            return false;
        }

        // Evitar duplicados
        $existe = $parteEmergencia->firmantes()
            ->where('personal_id', $parteEmergencia->personal_id)
            ->exists();

        if (!$existe) {
            $parteEmergencia->firmantes()->create([
                'personal_id' => $parteEmergencia->personal_id,
                'orden_firma' => 1,
                'marcador_firma' => '__FIRMA_OBAC__',
                'estado_firma' => 'PENDIENTE',
            ]);
        }

        return true;
    }    
}
