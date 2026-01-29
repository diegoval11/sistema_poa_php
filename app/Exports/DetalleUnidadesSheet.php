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

class DetalleUnidadesSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $unidades;

    public function __construct($unidades)
    {
        $this->unidades = $unidades;
    }

    public function collection()
    {
        $data = collect();
        
        $rowNumber = 1;
        foreach ($this->unidades as $unidad) {
            $data->push([
                $rowNumber++,
                $unidad->unidad->nombre ?? $unidad->name,
                $unidad->email,
                $unidad->total_proyectos,
                $unidad->count_proyectos_aprobados,
                $unidad->rendimiento . '%',
                $this->getCategoriaTexto($unidad->rendimiento),
            ]);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            '#',
            'Unidad',
            'Email',
            'Total Proyectos',
            'Aprobados',
            'Rendimiento',
            'Categoría',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 40,
            'C' => 30,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
        ];
    }

    public function title(): string
    {
        return 'Detalle';
    }

    public function styles(Worksheet $sheet)
    {
        // Style headers
        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1E40AF'); // Congress blue
        
        // Add borders to headers
        $sheet->getStyle('A1:G1')->applyFromArray([
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
        $sheet->getStyle('A2:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD1D5DB'],
                ],
            ],
        ]);
        
        // Alternating row colors and category colors
        for ($i = 2; $i <= $lastRow; $i++) {
            // Alternating rows
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':F' . $i)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF9FAFB'); // Light gray
            }
            
            // Category cell color based on performance
            $rendimiento = (float) str_replace('%', '', $sheet->getCell('F' . $i)->getValue());
            $categoriaColor = $this->getCategoriaColor($rendimiento);
            
            $sheet->getStyle('G' . $i)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($categoriaColor);
            
            // Make category text bold and white for better visibility
            if ($categoriaColor !== 'FFFFFFFF') {
                $sheet->getStyle('G' . $i)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            } else {
                $sheet->getStyle('G' . $i)->getFont()->setBold(true);
            }
        }
        
        // Center align specific columns
        $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
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
                $sheet->insertNewRowBefore(1, 2);
                
                // Section title
                $sheet->setCellValue('A1', 'DETALLE POR UNIDAD');
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E40AF');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }

    private function getCategoriaTexto($rendimiento)
    {
        if ($rendimiento >= 80) {
            return 'Excelente';
        } elseif ($rendimiento >= 60) {
            return 'Bueno';
        } elseif ($rendimiento >= 40) {
            return 'Regular';
        } else {
            return 'Bajo';
        }
    }

    private function getCategoriaColor($rendimiento)
    {
        if ($rendimiento >= 80) {
            return 'FF059669'; // Green
        } elseif ($rendimiento >= 60) {
            return 'FFEAB308'; // Yellow
        } elseif ($rendimiento >= 40) {
            return 'FFF97316'; // Orange
        } else {
            return 'FFDC2626'; // Red
        }
    }
}
