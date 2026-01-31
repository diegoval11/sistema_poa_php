<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MetasActividadesSheet implements FromArray, WithStyles, WithTitle, WithEvents
{
    protected $proyecto;
    protected $mesesNombres = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function __construct($proyecto)
    {
        $this->proyecto = $proyecto;
    }

    public function array(): array
    {
        $data = [];
        $data[] = ['METAS Y ACTIVIDADES DETALLADAS'];
        $data[] = ['']; // Empty row
        
        foreach ($this->proyecto->metas as $metaIndex => $meta) {
            // Meta header
            $data[] = ['META ' . ($metaIndex + 1) . ': ' . $meta->descripcion];
            $data[] = ['']; // Empty row
            
            // Activities table header
            $data[] = ['#', 'Actividad', 'U.M.', 'Cant.', 'Recursos'];
            
            // Activities
            foreach ($meta->actividades as $actIndex => $actividad) {
                $data[] = [
                    $actIndex + 1,
                    $actividad->descripcion,
                    $actividad->unidad_medida,
                    $actividad->cantidad_programada_total,
                    '$' . number_format($actividad->costo_estimado, 2)
                ];
                
                // Programming header for this activity
                $data[] = ['']; // Empty row
                $data[] = ['Programación: ' . $actividad->descripcion . '...'];
                $data[] = ['Mes', 'Prog.', 'Real.', '%', 'Estado'];
                
                // Programming data
                $programaciones = $actividad->programaciones->sortBy('mes');
                foreach ($programaciones as $prog) {
                    $cumplimiento = 0;
                    $estado = 'Deficiente';
                    
                    if ($prog->cantidad_programada > 0) {
                        $cumplimiento = round(($prog->cantidad_ejecutada / $prog->cantidad_programada) * 100, 2);
                        if ($cumplimiento >= 100) {
                            $estado = 'Excelente';
                        } elseif ($cumplimiento >= 100) {
                            $estado = 'Excelente';
                        } elseif ($cumplimiento >= 50) {
                            $estado = 'Bueno';
                        } else {
                            $estado = 'Deficiente';
                        }
                    }
                    
                    $data[] = [
                        $this->mesesNombres[$prog->mes],
                        $prog->cantidad_programada,
                        $prog->cantidad_ejecutada ?? 0,
                        $cumplimiento . '%',
                        $estado
                    ];
                }
                
                // Evidences
                if ($actividad->evidencias->count() > 0) {
                    $data[] = ['']; // Empty row
                    $data[] = ['Evidencias (' . $actividad->evidencias->count() . ')'];
                    $data[] = ['Tipo', 'Mes', 'Descripción', 'Fecha'];
                    
                    foreach ($actividad->evidencias as $evidencia) {
                        // Extract file extension from archivo path
                        $tipo = 'PDF';
                        if ($evidencia->archivo) {
                            $extension = pathinfo($evidencia->archivo, PATHINFO_EXTENSION);
                            $tipo = strtoupper($extension);
                        }
                        
                        // Get month name
                        $mes = $evidencia->mes ? $this->mesesNombres[$evidencia->mes] : 'N/A';
                        
                        $data[] = [
                            $tipo,
                            $mes,
                            $evidencia->descripcion ?? 'Sin descripción',
                            $evidencia->created_at->format('d/m/Y')
                        ];
                    }
                }
                
                $data[] = ['']; // Empty row between activities
            }
            
            $data[] = ['']; // Empty row between metas
        }
        
        return $data;
    }

    public function title(): string
    {
        return 'Metas y Actividades';
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                
                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(50);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(10);
                $sheet->getColumnDimension('E')->setWidth(15);
                
                // Style title
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E40AF');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->mergeCells('A1:E1');
                $sheet->getRowDimension(1)->setRowHeight(25);
                
                // Process each row for styling
                for ($i = 1; $i <= $lastRow; $i++) {
                    $cellValue = $sheet->getCell('A' . $i)->getValue();
                    
                    // Meta headers (starts with "META")
                    if (is_string($cellValue) && strpos($cellValue, 'META') === 0) {
                        $sheet->getStyle('A' . $i)->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
                        $sheet->mergeCells('A' . $i . ':E' . $i);
                        $sheet->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle('A' . $i)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FF1E40AF');
                        $sheet->getRowDimension($i)->setRowHeight(20);
                    }
                    
                    // Activity table headers (#, Actividad, U.M., Cant., Recursos)
                    elseif ($cellValue === '#') {
                        $sheet->getStyle('A' . $i . ':E' . $i)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                        $sheet->getStyle('A' . $i . ':E' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('A' . $i . ':E' . $i)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FF1E40AF');
                        $sheet->getStyle('A' . $i . ':E' . $i)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_MEDIUM,
                                    'color' => ['argb' => 'FF1E40AF'],
                                ],
                            ],
                        ]);
                    }
                    
                    // Programming subtitle (starts with "Programación:")
                    elseif (is_string($cellValue) && strpos($cellValue, 'Programación:') === 0) {
                        $sheet->getStyle('A' . $i)->getFont()->setBold(true)->setItalic(true)->setSize(10);
                        $sheet->mergeCells('A' . $i . ':E' . $i);
                    }
                    
                    // Programming headers (Mes, Prog., Real., %, Estado)
                    elseif ($cellValue === 'Mes') {
                        $sheet->getStyle('A' . $i . ':E' . $i)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                        $sheet->getStyle('A' . $i . ':E' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('A' . $i . ':E' . $i)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FF1E40AF');
                        $sheet->getStyle('A' . $i . ':E' . $i)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF1E40AF'],
                                ],
                            ],
                        ]);
                    }
                    
                    // Evidence subtitle (starts with "Evidencias")
                    elseif (is_string($cellValue) && strpos($cellValue, 'Evidencias') === 0) {
                        $sheet->getStyle('A' . $i)->getFont()->setBold(true)->setItalic(true)->setSize(10)->getColor()->setARGB('FF059669');
                        $sheet->mergeCells('A' . $i . ':E' . $i);
                    }
                    
                    // Evidence headers (Tipo, Mes, Descripción, Fecha)
                    elseif ($cellValue === 'Tipo') {
                        $sheet->getStyle('A' . $i . ':D' . $i)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                        $sheet->getStyle('A' . $i . ':D' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('A' . $i . ':D' . $i)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FF059669');
                        $sheet->getStyle('A' . $i . ':D' . $i)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF059669'],
                                ],
                            ],
                        ]);
                    }
                    
                    // Activity data rows (numeric in first column)
                    elseif (is_numeric($cellValue) && $cellValue > 0 && $cellValue < 100) {
                        // Check if this is an activity row (has description in B)
                        $bValue = $sheet->getCell('B' . $i)->getValue();
                        if (!empty($bValue) && strlen($bValue) > 10) {
                            $sheet->getStyle('A' . $i . ':E' . $i)->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['argb' => 'FFD1D5DB'],
                                    ],
                                ],
                            ]);
                            $sheet->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                    }
                    
                    // Programming data rows (month names)
                    elseif (in_array($cellValue, $this->mesesNombres)) {
                        $sheet->getStyle('A' . $i . ':E' . $i)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FFD1D5DB'],
                                ],
                            ],
                        ]);
                        $sheet->getStyle('A' . $i . ':E' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        
                        // Color code Estado column based on value
                        $estadoValue = $sheet->getCell('E' . $i)->getValue();
                        if ($estadoValue === 'Excelente') {
                            $sheet->getStyle('E' . $i)->getFont()->getColor()->setARGB('FF059669');
                            $sheet->getStyle('E' . $i)->getFont()->setBold(true);
                        } elseif ($estadoValue === 'Deficiente') {
                            $sheet->getStyle('E' . $i)->getFont()->getColor()->setARGB('FFDC2626');
                            $sheet->getStyle('E' . $i)->getFont()->setBold(true);
                        }
                    }
                    
                    // Evidence data rows (file type in first column like PDF, XLSX, etc.)
                    elseif (is_string($cellValue) && in_array(strtoupper($cellValue), ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG', 'JPEG', 'DOC', 'XLS'])) {
                        $sheet->getStyle('A' . $i . ':D' . $i)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FFD1D5DB'],
                                ],
                            ],
                        ]);
                        $sheet->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('D' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
            },
        ];
    }
}
