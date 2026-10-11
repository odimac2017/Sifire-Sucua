<?php

namespace App\Observers;

use App\Jobs\GenerarPdfInformeDiarioJob;
use App\Models\InformeDiario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InformeDiarioObserver
{
    public function created(InformeDiario $informeDiario): void
    {        
        if ($this->gestionarFirmantes($informeDiario)) {
            GenerarPdfInformeDiarioJob::dispatch($informeDiario->id)
                ->onQueue('pdf');
        }
    }

    public function updated(InformeDiario $informeDiario): void
    {        
        if (
            $informeDiario->wasChanged('estado_firma') &&
            $informeDiario->estado_firma === 'PENDIENTE'
        ) {
            $archivo = $informeDiario->getOriginal('ruta_pdf_firmado');

            DB::transaction(function () use ($informeDiario) {
                $informeDiario->firmantes()
                    ->update([
                        'estado_firma' => 'PENDIENTE',
                        'fecha_firma' => null,
                    ]);

                $informeDiario->updateQuietly([
                    'ruta_pdf_firmado' => null,
                ]);
            });

            if (!empty($archivo)) {
                Storage::disk('private')->delete($archivo);
            }

            return;
        }
    }

    public function deleted(InformeDiario $informeDiario): void
    {
        $archivos = array_filter([
            $informeDiario->ruta_pdf,
            $informeDiario->ruta_pdf_firmado
        ]);

        if (!empty($archivos)) {
            Storage::disk('private')->delete($archivos);
        }
    }

    private function gestionarFirmantes(InformeDiario $informeDiario): bool
    {
        $informeDiario->loadMissing('personal.firma');

        $firma = $informeDiario->personal->firma;

        if (!$firma || !$firma->ruta) {
            $informeDiario->updateQuietly([
                'estado_firma' => 'SIN_FIRMA',
            ]);

            return false;
        }

        // Evitar duplicados
        $existe = $informeDiario->firmantes()
            ->where('personal_id', $informeDiario->personal_id)
            ->exists();

        if (!$existe) {
            $informeDiario->firmantes()->create([
                'personal_id' => $informeDiario->personal_id,
                'orden_firma' => 1,
                'marcador_firma' => '__FIRMA_RESPONSABLE__',
                'estado_firma' => 'PENDIENTE',
            ]);
        }

        return true;
    }    
}
