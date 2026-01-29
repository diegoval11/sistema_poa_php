<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ReporteTrimestralExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $unidades;
    protected $fechaGeneracion;
    protected $usuarioGenerador;

    public function __construct($unidades, $fechaGeneracion, $usuarioGenerador)
    {
        $this->unidades = $unidades;
        $this->fechaGeneracion = $fechaGeneracion;
        $this->usuarioGenerador = $usuarioGenerador;
    }

    public function collection()
    {
        $data = collect();
        
        // Add table data only (metadata will be added in afterSheet event)
        $rowNumber = 1;
        foreach ($this->unidades as $unidad) {
            $data->push([
                $rowNumber++,
                $unidad->unidad->nombre ?? $unidad->name,
                $unidad->quarters[1] . '%',
                $unidad->quarters[2] . '%',
                $unidad->quarters[3] . '%',
                $unidad->quarters[4] . '%',
            ]);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            '#',
            'Unidad',
            'Trimestre 1',
            'Trimestre 2',
            'Trimestre 3',
            'Trimestre 4',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 50,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style headers
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1E40AF'); // Congress blue color
        
        // Add borders to headers
        $sheet->getStyle('A1:F1')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF1E40AF'],
                ],
            ],
        ]);
        
        // Style data rows
        $lastRow = 1 + $this->unidades->count();
        
        // Add borders to all data
        $sheet->getStyle('A2:F' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD1D5DB'],
                ],
            ],
        ]);
        
        // Alternating row colors for better readability
        for ($i = 2; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':F' . $i)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF9FAFB'); // Light gray
            }
        }
        
        // Center align numbers
        $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Make row height taller for better spacing
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert metadata rows at the top
                $sheet->insertNewRowBefore(1, 5);
                
                // Title
                $sheet->setCellValue('A1', 'REPORTE DE CUMPLIMIENTO TRIMESTRAL');
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->getColor()->setARGB('FF1E40AF');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // Metadata section with light blue background
                $sheet->setCellValue('A3', 'Fecha de Generación:');
                $sheet->setCellValue('B3', $this->fechaGeneracion);
                $sheet->setCellValue('A4', 'Generado por:');
                $sheet->setCellValue('B4', $this->usuarioGenerador);
                
                $sheet->getStyle('A3:A4')->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle('A3:B4')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDBEAFE'); // Light purple/blue
                $sheet->getStyle('A3:B4')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF7C3AED'],
                        ],
                    ],
                ]);
                
                // Section title
                $sheet->setCellValue('A5', 'DETALLE POR UNIDAD');
                $sheet->mergeCells('A5:F5');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FF1E40AF');
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getRowDimension(5)->setRowHeight(20);
                
                // Footer with total
                $lastRow = 6 + $this->unidades->count();
                $footerRow = $lastRow + 2;
                $sheet->setCellValue('A' . $footerRow, 'Total de unidades reportadas: ' . $this->unidades->count());
                $sheet->mergeCells('A' . $footerRow . ':F' . $footerRow);
                $sheet->getStyle('A' . $footerRow)->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A' . $footerRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF1E40AF');
                $sheet->getStyle('A' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($footerRow)->setRowHeight(25);
            },
        ];
    }
}

