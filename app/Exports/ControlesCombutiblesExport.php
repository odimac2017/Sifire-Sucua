<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ControlesCombutiblesExport implements 
    FromCollection, 
    WithHeadings, 
    WithStyles, 
    ShouldAutoSize, 
    WithEvents,
    WithCustomStartCell
{
    protected $institucion;
    protected $periodo;
    protected $reportes;

    public function __construct($institucion, $periodo, $reportes)
    {
        $this->institucion = $institucion;    
        $this->periodo = $periodo;    
        $this->reportes = $reportes;       
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function collection()
    {
        $galones = number_format($this->reportes->sum('cantidad_galones'),2);
    
        $total = number_format($this->reportes->sum('valor_total'),2);

        $rows = $this->reportes->map(function ($reporte, $index) {

            return [
                $index + 1,
                $reporte->fecha,
                $reporte->combustible->nombre,
                $reporte->nombre_chofer,
                $reporte->bien->identificacion,
                $reporte->numero_formateado,
                $reporte->km_recorrido > 0 ? $reporte->km_recorrido : '',
                number_format($reporte->cantidad_galones, 2),
                number_format($reporte->valor_total, 2),
                $reporte->estado,
            ];
        });

        $rows->push([
            'TOTALES',
            '',
            '',
            '',
            '',
            '',
            '',
            $galones,
            $total,
            ''
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            '#',
            'Fecha',
            'Combustible',
            'Chofer',
            'Vehículo / equipo',
            'Número de orden',
            'Km de recorrido',
            'Galones',
            'Precio total',
            'Estado'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // 🖼️ LOGO1
                $drawing = new Drawing();
                $drawing->setPath(public_path('images/logo1.jpg'));
                $drawing->setHeight(80);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);

                // 🖼️ LOGO2
                $drawing = new Drawing();
                $drawing->setPath(public_path('images/logo2.jpg'));
                $drawing->setHeight(80);
                $drawing->setCoordinates('J1');
                $drawing->setOffsetX(10);
                $drawing->setWorksheet($sheet);

                // 🏛️ RAZÓN SOCIAL
                $sheet->mergeCells('A1:J1');
                $sheet->setCellValue('A1', $this->institucion->razon_social);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // 🧾 RUC
                $sheet->mergeCells('A2:J2');
                $sheet->setCellValue('A2', 'RUC: ' . $this->institucion->ruc);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // 📑 REPORTE
                $sheet->mergeCells('A3:J3');
                $sheet->setCellValue('A3', 'REPORTE DE CONTROL DE COMBUSTIBLE');
                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->getStyle('A3')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // 📅 PERÍODO  
                $sheet->mergeCells('A4:J4');
                $sheet->setCellValue('A4', mb_strtoupper($this->periodo));
                $sheet->getStyle('A4')->getFont()->setBold(true);
                $sheet->getStyle('A4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // 🔢 última fila REAL
                $lastRow = $sheet->getHighestDataRow();

                // 🎨 encabezado
                $sheet->getStyle('A6:J6')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'],
                    ],
                ]);

                // 🧱 bordes
                $sheet->getStyle("A6:J{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // 💰 moneda
                $sheet->getStyle("I7:I{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

                // 🎯 ALINEACIÓN GLOBAL (CLAVE)
                $sheet->getStyle("A6:J{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // 🔁 recalcular altura (CLAVE para que no quede abajo)
                foreach (range(7, $lastRow) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }

                // 🔗 combinar A → G
                $sheet->mergeCells("A{$lastRow}:G{$lastRow}");                

                // 🎯 alinear texto
                $sheet->getStyle("A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // 🎨 fondo
                $sheet->getStyle("A{$lastRow}:J{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF2CC'],
                    ],
                ]);

                // 🔠 negrita + tamaño
                $sheet->getStyle("A{$lastRow}:I{$lastRow}")
                    ->getFont()->setBold(true)->setSize(12);

                // 💰 TOTAL
                $sheet->getStyle("H{$lastRow}:I{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF2CC'],
                    ],
                ]);
            },
        ];
    }
}