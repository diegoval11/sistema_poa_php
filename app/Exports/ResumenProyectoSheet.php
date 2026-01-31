<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResumenProyectoSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $proyecto;
    protected $fechaGeneracion;

    public function __construct($proyecto, $fechaGeneracion)
    {
        $this->proyecto = $proyecto;
        $this->fechaGeneracion = $fechaGeneracion;
    }

    public function collection()
    {
        $data = [];
        
        // Información General
        $data[] = ['Objetivo de la Unidad:', $this->proyecto->objetivo_unidad ?? 'N/A'];
        $data[] = ['Fecha de Creación:', $this->proyecto->created_at->format('d/m/Y H:i')];
        $data[] = ['Última Modificación:', $this->proyecto->updated_at->format('d/m/Y H:i')];
        
        if ($this->proyecto->estado === 'APROBADO' && $this->proyecto->aprobado_por) {
            $aprobador = \App\Models\User::find($this->proyecto->aprobado_por);
            $data[] = ['Aprobado por:', $aprobador ? $aprobador->email : 'N/A'];
            $data[] = ['Fecha de Aprobación:', $this->proyecto->fecha_aprobacion ? $this->proyecto->fecha_aprobacion->format('d/m/Y H:i') : 'N/A'];
        }
        
        $data[] = ['', '']; // Empty row
        
        // Resumen Ejecutivo
        $totalMetas = $this->proyecto->metas->count();
        $totalActividades = $this->proyecto->metas->sum(function($meta) {
            return $meta->actividades->count();
        });
        $presupuestoTotal = $this->proyecto->metas->sum(function($meta) {
            return $meta->actividades->sum('costo_estimado');
        });
        $totalEvidencias = $this->proyecto->metas->sum(function($meta) {
            return $meta->actividades->sum(function($actividad) {
                return $actividad->evidencias->count();
            });
        });
        
        $data[] = ['Total de Metas:', $totalMetas];
        $data[] = ['Total de Actividades:', $totalActividades];
        $data[] = ['Presupuesto Total:', '$' . number_format($presupuestoTotal, 2)];
        $data[] = ['Total de Evidencias:', $totalEvidencias];
        
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Campo',
            'Valor',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 50,
        ];
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function styles(Worksheet $sheet)
    {
        // Style headers
        $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:B1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1E40AF'); // Congress blue
        
        // Add borders to headers
        $sheet->getStyle('A1:B1')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF1E40AF'],
                ],
            ],
        ]);
        
        // Row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert title rows at the top
                $sheet->insertNewRowBefore(1, 6);
                
                // Title
                $sheet->setCellValue('A1', 'PLAN OPERATIVO ANUAL (POA)');
                $sheet->mergeCells('A1:B1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->getColor()->setARGB('FF1E40AF');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // Project Info
                $sheet->setCellValue('A2', 'Proyecto: ' . $this->proyecto->nombre);
                $sheet->mergeCells('A2:B2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                
                $sheet->setCellValue('A3', 'Unidad Responsable: ' . $this->proyecto->unidad->unidad->nombre);
                $sheet->mergeCells('A3:B3');
                $sheet->getStyle('A3')->getFont()->setSize(11);
                
                $sheet->setCellValue('A4', 'Año de Ejecución: ' . $this->proyecto->anio);
                $sheet->mergeCells('A4:B4');
                $sheet->getStyle('A4')->getFont()->setSize(11);
                
                $sheet->setCellValue('A5', 'Estado: ' . $this->proyecto->estado);
                $sheet->mergeCells('A5:B5');
                $sheet->getStyle('A5')->getFont()->setSize(11);
                
                $sheet->setCellValue('A6', 'Fecha de Generación: ' . $this->fechaGeneracion);
                $sheet->mergeCells('A6:B6');
                $sheet->getStyle('A6')->getFont()->setSize(10)->getColor()->setARGB('FF6B7280');
                
                // Section title for "Información General" (row 7)
                $currentRow = 7;
                $sheet->setCellValue('A' . $currentRow, 'INFORMACIÓN GENERAL');
                $sheet->mergeCells('A' . $currentRow . ':B' . $currentRow);
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A' . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF1E40AF');
                $sheet->getRowDimension($currentRow)->setRowHeight(20);
                
                // Find the empty row in data and insert "Resumen Ejecutivo" title
                $lastRow = $sheet->getHighestRow();
                for ($i = 8; $i <= $lastRow; $i++) {
                    $cellValue = $sheet->getCell('A' . $i)->getValue();
                    if (empty($cellValue) && $i > 10) {
                        $sheet->setCellValue('A' . $i, 'RESUMEN EJECUTIVO');
                        $sheet->mergeCells('A' . $i . ':B' . $i);
                        $sheet->getStyle('A' . $i)->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
                        $sheet->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle('A' . $i)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FF1E40AF');
                        $sheet->getRowDimension($i)->setRowHeight(20);
                        break;
                    }
                }
                
                // Style data rows with borders
                for ($i = 8; $i <= $lastRow; $i++) {
                    $sheet->getStyle('A' . $i . ':B' . $i)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFD1D5DB'],
                            ],
                        ],
                    ]);
                    
                    // Bold the labels in column A
                    $sheet->getStyle('A' . $i)->getFont()->setBold(true);
                }
            },
        ];
    }
}
