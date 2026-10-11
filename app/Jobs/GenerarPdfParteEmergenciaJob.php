<?php

namespace App\Jobs;

use App\Models\Institucion;
use App\Models\ParteEmergencia;
use App\Models\Plantilla;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GenerarPdfParteEmergenciaJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $parteEmergenciaId;

    public function __construct($parteEmergenciaId)
    {
        $this->parteEmergenciaId = $parteEmergenciaId;
    }

    public function handle()
    {
        Log::info("Inicio Job ID: {$this->parteEmergenciaId}");

        try {

            $parteEmergencia = ParteEmergencia::find($this->parteEmergenciaId);

            if (!$parteEmergencia) {
                Log::error("Parte de emergencia no encontrada Id {$this->parteEmergenciaId}");
                return;
            }            
            
            $institucion = Institucion::first();
            $plantilla = Plantilla::find($parteEmergencia->plantilla_id);

            $vista = 'pdf.' . $plantilla->vista_blade;

            $pdf = Pdf::loadView($vista, compact('institucion','parteEmergencia'));
            
            $pdf->setPaper(
                $plantilla->tamano_papel,
                $plantilla->orientacion_papel ?? 'portrait'
            );            

            $pdf->render();

            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->getCanvas();
            $fontMetrics = $dompdf->getFontMetrics();

            $fontNormal = $fontMetrics->getFont("Roboto", "normal");

            $canvas->page_text(30, 810, "Página {PAGE_NUM} de {PAGE_COUNT}", $fontNormal, 8, [0,0,0]);

            $year = Carbon::parse($parteEmergencia->fecha_llamada)->year;

            $file = "partes_emergencias/{$year}/{$parteEmergencia->numero}.pdf";

            // Crear carpeta si no existe
            Storage::makeDirectory("partes_emergencias/{$year}");

            // Guardar PDF
            Storage::put($file, $pdf->output());

            $parteEmergencia->update([
                'ruta_pdf' => $file,
                'ruta_pdf_firmado' => null,
                'estado_firma' => 'PENDIENTE'
            ]);

            FirmarDocumentoJob::dispatch(
                \App\Models\ParteEmergencia::class,
                $parteEmergencia->id
            )->onQueue('firmas');

        } catch (\Exception $e) {
            
            Log::error("Error generando PDF inspección", [
                'parte_emergencia_id' => $this->parteEmergenciaId,
                'error' => $e->getMessage()
            ]);
        }
    }
}