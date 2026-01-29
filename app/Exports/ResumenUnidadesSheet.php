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

class ResumenUnidadesSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $fechaGeneracion;
    protected $resumen;

    public function __construct($fechaGeneracion, $resumen)
    {
        $this->fechaGeneracion = $fechaGeneracion;
        $this->resumen = $resumen;
    }

    public function collection()
    {
        return collect([
            ['Total de Unidades', $this->resumen['total'], '100%'],
            ['Excelente (≥80%)', $this->resumen['excelente'], number_format(($this->resumen['excelente'] / max($this->resumen['total'], 1)) * 100, 1) . '%'],
            ['Bueno (60-79%)', $this->resumen['bueno'], number_format(($this->resumen['bueno'] / max($this->resumen['total'], 1)) * 100, 1) . '%'],
            ['Regular (40-59%)', $this->resumen['regular'], number_format(($this->resumen['regular'] / max($this->resumen['total'], 1)) * 100, 1) . '%'],
            ['Bajo (<40%)', $this->resumen['bajo'], number_format(($this->resumen['bajo'] / max($this->resumen['total'], 1)) * 100, 1) . '%'],
        ]);
    }

    public function headings(): array
    {
        return [
            'Categoría',
            'Cantidad',
            'Porcentaje',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 15,
        ];
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function styles(Worksheet $sheet)
    {
        // Style headers
        $sheet->getStyle('A1:C1')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1E40AF'); // Congress blue
        
        // Add borders to headers
        $sheet->getStyle('A1:C1')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF1E40AF'],
                ],
            ],
        ]);
        
        // Add borders to data
        $sheet->getStyle('A2:C6')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD1D5DB'],
                ],
            ],
        ]);
        
        // Center align all cells
        $sheet->getStyle('A2:C6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Style total row (row 2)
        $sheet->getStyle('A2:C2')->getFont()->setBold(true);
        $sheet->getStyle('A2:C2')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E7FF'); // Light blue
        
        // Alternating row colors
        for ($i = 3; $i <= 6; $i++) {
            if ($i % 2 == 1) {
                $sheet->getStyle('A' . $i . ':C' . $i)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF9FAFB'); // Light gray
            }
        }
        
        // Row height
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert title rows at the top
                $sheet->insertNewRowBefore(1, 3);
                
                // Title
                $sheet->setCellValue('A1', 'REPORTE DE UNIDADES Y CUMPLIMIENTO');
                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->getColor()->setARGB('FF1E40AF');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // Date
                $sheet->setCellValue('A2', 'Fecha de Generación: ' . $this->fechaGeneracion);
                $sheet->mergeCells('A2:C2');
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // Section title
                $sheet->setCellValue('A3', 'RESUMEN GENERAL');
                $sheet->mergeCells('A3:C3');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FF1E40AF');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getRowDimension(3)->setRowHeight(20);
            },
        ];
    }
}
