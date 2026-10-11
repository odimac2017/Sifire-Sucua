<?php

namespace App\Jobs;

use App\Models\InformeDiario;
use App\Models\Institucion;
use App\Models\Plantilla;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GenerarPdfInformeDiarioJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $informeDiarioId;

    public function __construct($informeDiarioId)
    {
        $this->informeDiarioId = $informeDiarioId;
    }

    public function handle()
    {
        Log::info("Inicio Job ID: {$this->informeDiarioId}");

        try {

            $informeDiario = InformeDiario::find($this->informeDiarioId);

            if (!$informeDiario) {
                Log::error("Informe diariop no encontrado Id {$this->informeDiarioId}");
                return;
            }            
            
            $institucion = Institucion::first();
            $plantilla = Plantilla::find($informeDiario->plantilla_id);

            $vista = 'pdf.' . $plantilla->vista_blade;

            $pdf = Pdf::loadView($vista, compact('institucion','informeDiario'));
            
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

            $year = Carbon::parse($informeDiario->fecha_informe)->year;

            $file = "informes_diarios/{$year}/{$informeDiario->numero}.pdf";

            // Crear carpeta si no existe
            Storage::makeDirectory("informes_diarios/{$year}");

            // Guardar PDF
            Storage::put($file, $pdf->output());

            $informeDiario->update([
                'ruta_pdf' => $file,
                'ruta_pdf_firmado' => null,
                'estado_firma' => 'PENDIENTE'
            ]);

            FirmarDocumentoJob::dispatch(
                \App\Models\InformeDiario::class,
                $informeDiario->id
            )->onQueue('firmas');

        } catch (\Exception $e) {
            
            Log::error("Error generando PDF", [
                'informe_diario_id' => $this->informeDiarioId,
                'error' => $e->getMessage()
            ]);
        }
    }
}