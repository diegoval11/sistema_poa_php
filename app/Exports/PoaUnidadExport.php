<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PoaUnidadExport implements FromCollection, WithEvents, WithStyles, WithColumnWidths, WithTitle
{
    protected $unidad;
    protected $proyectos;
    protected $proyectoNoPlanificado;
    protected $objetivoEstrategico;
    protected $anio;
    protected $totalRows = 0;
    protected $startDataRow = 15;

    public function __construct($unidad, $proyectos, $proyectoNoPlanificado, $objetivoEstrategico, $anio)
    {
        $this->unidad = $unidad;
        $this->proyectos = $proyectos;
        $this->proyectoNoPlanificado = $proyectoNoPlanificado;
        $this->objetivoEstrategico = $objetivoEstrategico;
        $this->anio = $anio;

        $count = 0;
        foreach ($proyectos as $p) {
            foreach ($p->metas as $m) {
                $count += $m->actividades->count();
            }
        }
        if ($proyectoNoPlanificado) {
            foreach ($proyectoNoPlanificado->metas as $m) {
                $count += $m->actividades->count();
            }
        }
        $this->totalRows = $count;
    }

    public function collection()
    {
        return collect([]);
    }

    public function title(): string
    {
        return "POA {$this->anio}";
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 2, 'B' => 6, 'C' => 30, 'D' => 30, 'E' => 45, 'F' => 15,
        ];

        $col = 7;
        foreach (range(1, 12) as $mes) {
            $widths[Coordinate::stringFromColumnIndex($col++)] = 12;
            $widths[Coordinate::stringFromColumnIndex($col++)] = 12;
            $widths[Coordinate::stringFromColumnIndex($col++)] = 13;
            $widths[Coordinate::stringFromColumnIndex($col++)] = 25;

            if ($mes % 3 == 0) {
                $widths[Coordinate::stringFromColumnIndex($col++)] = 15;
            }
        }

        $widths[Coordinate::stringFromColumnIndex($col++)] = 15;
        $widths[Coordinate::stringFromColumnIndex($col++)] = 15;
        $widths[Coordinate::stringFromColumnIndex($col++)] = 15;
        $widths[Coordinate::stringFromColumnIndex($col++)] = 20;

        return $widths;
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
                $this->buildStructure($sheet);
                $lastRow = $this->buildData($sheet);
                if ($this->proyectoNoPlanificado) {
                    $this->buildUnplanned($sheet, $lastRow);
                }
            },
        ];
    }

    protected function buildStructure($sheet)
    {
        $sheet->mergeCells('B2:F2');
        $sheet->setCellValue('B2', "FORMATO POA {$this->anio}      PLAN OPERATIVO ANUAL {$this->anio} (POA {$this->anio})");
        $sheet->getStyle('B2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_BOTTOM],
        ]);
        
        $sheet->setCellValue('E1', 'ALCALDÍA MUNICIPAL DE SANTA ANA CENTRO');
        $sheet->getStyle('E1')->getFont()->setBold(true)->setSize(14)->setName('Times New Roman');

        $baseStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->setCellValue('B5', '1');
        $sheet->mergeCells('C5:S5');
        $sheet->setCellValue('C5', "UNIDAD: " . strtoupper($this->unidad->unidad->nombre));
        $sheet->getStyle('B5')->applyFromArray(array_merge($baseStyle, ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]));
        $sheet->getStyle('C5:S5')->applyFromArray($baseStyle);
        $sheet->getStyle('C5:S5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEBF1DE');

        $sheet->setCellValue('B6', '2');
        $sheet->mergeCells('C6:S6');
        $sheet->setCellValue('C6', "OBJETIVO ESTRATÉGICO: " . $this->objetivoEstrategico->description);
        $sheet->getStyle('B6')->applyFromArray(array_merge($baseStyle, ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]));
        $sheet->getStyle('C6:S6')->applyFromArray($baseStyle);
        $sheet->getStyle('C6:S6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
        $sheet->getRowDimension(6)->setRowHeight(40);

        $objetivos = "";
        foreach ($this->proyectos as $p) {
            foreach ($p->metas as $m) $objetivos .= "- " . $m->descripcion . "\n";
        }

        $sheet->setCellValue('B7', '3');
        $sheet->mergeCells('C7:S7');
        $sheet->setCellValue('C7', "OBJETIVO DE LA UNIDAD:\n" . $objetivos);
        $sheet->getStyle('B7')->applyFromArray(array_merge($baseStyle, ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]));
        $sheet->getStyle('C7:S7')->applyFromArray($baseStyle);
        $sheet->getStyle('C7:S7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF8EB4E3');
        $sheet->getStyle('C7')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(7)->setRowHeight(60);

        $darkFill = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF17375E']]];
        $whiteFont = ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Times New Roman', 'size' => 9]];
        $border = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        $sheet->setCellValue('G10', '8. Programación');
        $sheet->getStyle('G10')->applyFromArray(array_merge($darkFill, $whiteFont));

        $quarters = ['(Q1) TRIMESTRE 1', '(Q2) TRIMESTRE 2', '(Q3) TRIMESTRE 3', '(Q4) TRIMESTRE 4'];
        $col = 7;
        
        foreach ($quarters as $qTitle) {
            $start = Coordinate::stringFromColumnIndex($col);
            $end = Coordinate::stringFromColumnIndex($col + 3);
            
            $sheet->mergeCells("{$start}11:{$end}11");
            $sheet->setCellValue("{$start}11", $qTitle);
            $sheet->getStyle("{$start}11")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF376092']],
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Times New Roman'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            $col += 12; 
            $col++; 
        }

        $rowEff = 12;
        $sheet->getRowDimension($rowEff)->setRowHeight(30);
        $col = 7;
        
        for($q=0; $q<4; $q++) {
            $col += 12;
            $colLetter = Coordinate::stringFromColumnIndex($col);
            
            $rangeStart = $colLetter . $this->startDataRow;
            $rangeEnd = $colLetter . ($this->startDataRow + $this->totalRows + 20); 
            
            $sheet->setCellValue("{$colLetter}{$rowEff}", "=IFERROR(AVERAGE({$rangeStart}:{$rangeEnd}),0)");
            $sheet->getStyle("{$colLetter}{$rowEff}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF254061']],
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            $sheet->getStyle("{$colLetter}{$rowEff}")->getNumberFormat()->setFormatCode('0%');
            $col++;
        }

        foreach (['B'=>'N°', 'C'=>'4.PROYECTOS', 'D'=>'5. METAS', 'E'=>'6. ACTIVIDAD', 'F'=>'7. UNIDAD DE MEDIDA'] as $c => $t) {
            $sheet->setCellValue("{$c}13", $t);
            $sheet->getStyle("{$c}13")->applyFromArray(array_merge($darkFill, $whiteFont, $border, ['alignment' => ['wrapText' => true, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]));
        }

        $meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
        $col = 7;
        foreach ($meses as $idx => $mes) {
            $cStart = Coordinate::stringFromColumnIndex($col);
            $cEnd = Coordinate::stringFromColumnIndex($col + 3);
            $sheet->mergeCells("{$cStart}13:{$cEnd}13");
            $sheet->setCellValue("{$cStart}13", $mes);
            $sheet->getStyle("{$cStart}13")->applyFromArray(array_merge($darkFill, $whiteFont, $border, ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]));

            $subs = ['Programado', 'Realizado', 'Cumplimiento', "Medio de verificación\no\nCausal de\nIncumplimiento"];
            for($i=0; $i<4; $i++) {
                $cc = Coordinate::stringFromColumnIndex($col + $i);
                $sheet->setCellValue("{$cc}14", $subs[$i]);
                $sheet->getStyle("{$cc}14")->applyFromArray(array_merge($darkFill, $whiteFont, $border, [
                    'alignment' => ['wrapText' => true, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'font' => ['size' => 8, 'color' => ['argb' => 'FFFFFFFF']]
                ]));
            }
            $col += 4;

            if (($idx+1) % 3 == 0) {
                $cQ = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$cQ}13", "Seguimiento\nTrimestral");
                $sheet->mergeCells("{$cQ}13:{$cQ}14");
                $sheet->getStyle("{$cQ}13")->applyFromArray(array_merge($darkFill, $whiteFont, $border, ['alignment' => ['wrapText' => true, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]));
                $col++;
            }
        }
        
        $finals = ["Seguimiento\nSemestral 1", "Seguimiento\nSemestral 2", "Promedio\nAnual", "Total de\nRecursos $"];
        foreach ($finals as $fText) {
            $cc = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$cc}13", $fText);
            $sheet->mergeCells("{$cc}13:{$cc}14");
            $sheet->getStyle("{$cc}13")->applyFromArray(array_merge($darkFill, $whiteFont, $border, ['alignment' => ['wrapText' => true, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]));
            $col++;
        }
    }

    protected function buildData($sheet)
    {
        $fila = $this->startDataRow;
        $num = 1;
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        foreach ($this->proyectos as $proyecto) {
            $filaInicioProy = $fila;
            
            foreach ($proyecto->metas as $meta) {
                $filaInicioMeta = $fila;
                
                foreach ($meta->actividades as $actividad) {
                    $this->fillActivityRow($sheet, $actividad, $fila, $num, $borderThin);
                    $num++;
                    $fila++;
                }
                
                if ($fila > $filaInicioMeta) {
                    $sheet->mergeCells("D{$filaInicioMeta}:D".($fila-1));
                    $sheet->setCellValue("D{$filaInicioMeta}", $meta->descripcion);
                    $sheet->getStyle("D{$filaInicioMeta}")->applyFromArray(array_merge($borderThin, ['alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true]]));
                }
            }
            
            if ($fila > $filaInicioProy) {
                $sheet->mergeCells("C{$filaInicioProy}:C".($fila-1));
                $sheet->setCellValue("C{$filaInicioProy}", $proyecto->nombre);
                $sheet->getStyle("C{$filaInicioProy}")->applyFromArray(array_merge($borderThin, ['alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true]]));
            }
        }
        return $fila;
    }

    protected function buildUnplanned($sheet, $startRow)
    {
        $fila = $startRow;
        
        // Cabecera "ACTIVIDADES NO PLANIFICADAS"
        $sheet->mergeCells("B{$fila}:F{$fila}");
        $sheet->setCellValue("B{$fila}", "ACTIVIDADES NO PLANIFICADAS");
        $sheet->getStyle("B{$fila}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF808080']], // Gris
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        
        $fila++;
        $num = 1;
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        // Se usa el objeto proyectoNoPlanificado como contenedor principal
        $proyecto = $this->proyectoNoPlanificado;
        if(!$proyecto) return;

        $filaInicioProy = $fila;

        foreach ($proyecto->metas as $meta) {
            $filaInicioMeta = $fila;
            
            foreach ($meta->actividades as $actividad) {
                $this->fillActivityRow($sheet, $actividad, $fila, $num, $borderThin);
                $num++;
                $fila++;
            }

            if ($fila > $filaInicioMeta) {
                $sheet->mergeCells("D{$filaInicioMeta}:D".($fila-1));
                $sheet->setCellValue("D{$filaInicioMeta}", $meta->descripcion);
                $sheet->getStyle("D{$filaInicioMeta}")->applyFromArray(array_merge($borderThin, ['alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true]]));
            }
        }

        if ($fila > $filaInicioProy) {
            $sheet->mergeCells("C{$filaInicioProy}:C".($fila-1));
            $sheet->setCellValue("C{$filaInicioProy}", "ACTIVIDADES NO PLANIFICADAS");
            $sheet->getStyle("C{$filaInicioProy}")->applyFromArray(array_merge($borderThin, [
                'font' => ['bold' => true],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true]
            ]));
        }
    }

    // Función auxiliar para no repetir código entre Data y Unplanned
    protected function fillActivityRow($sheet, $actividad, $fila, $num, $borderThin) 
    {
        $sheet->setCellValue("B{$fila}", $num);
        $sheet->setCellValue("E{$fila}", $actividad->descripcion);
        $sheet->setCellValue("F{$fila}", $actividad->unidad_medida);
        
        $col = 7;
        $qCells = [];
        $s1Cells = [];
        $s2Cells = [];
        
        for ($m=1; $m<=12; $m++) {
            $prog = $actividad->programaciones->where('mes', $m)->first();
            $cantP = $prog ? $prog->cantidad_programada : 0;
            $cantR = $prog ? $prog->cantidad_ejecutada : 0;
            
            $colP = Coordinate::stringFromColumnIndex($col);
            $colR = Coordinate::stringFromColumnIndex($col+1);
            $colC = Coordinate::stringFromColumnIndex($col+2);
            
            if ($cantP > 0) {
                $sheet->setCellValue("{$colP}{$fila}", $cantP);
                $sheet->setCellValue("{$colR}{$fila}", $cantR);
                
                $formula = "=IFERROR(IF({$colR}{$fila}/{$colP}{$fila}<=1,{$colR}{$fila}/{$colP}{$fila},\"El valor excede al 100%, colocar el valor programado y trasladar el excedente a actividades no programadas en este mes\"),\"Actividad no programada\")";
                
                $sheet->setCellValue("{$colC}{$fila}", $formula);
                $sheet->getStyle("{$colC}{$fila}")->getNumberFormat()->setFormatCode('0%');
                $qCells[] = $colC; // Store only column letter, not full cell reference
            } else {
                $sheet->setCellValue("{$colC}{$fila}", "Actividad no programada");
                $sheet->getStyle("{$colC}{$fila}")->applyFromArray([
                    'font' => ['size' => 8, 'color' => ['argb' => 'FF808080']]
                ]);
            }
            
            $col += 4;

            if ($m % 3 == 0) {
                $colQ = Coordinate::stringFromColumnIndex($col);
                if (count($qCells) > 0) {
                    // Build cell references for current row using stored column letters
                    $cellRefs = array_map(fn($c) => "{$c}{$fila}", $qCells);
                    $avg = implode(',', $cellRefs);
                    $sheet->setCellValue("{$colQ}{$fila}", "=IFERROR(AVERAGE({$avg}),0)");
                    if($m <= 6) $s1Cells[] = $colQ; // Store only column letter
                    else $s2Cells[] = $colQ; // Store only column letter
                } else {
                    $sheet->setCellValue("{$colQ}{$fila}", "-");
                }
                $sheet->getStyle("{$colQ}{$fila}")->getNumberFormat()->setFormatCode('0%');
                $qCells = [];
                $col++;
            }
        }

        $colS1 = Coordinate::stringFromColumnIndex($col++);
        if(count($s1Cells)) {
            // Build cell references for current row using stored column letters
            $refs = array_map(fn($c) => "{$c}{$fila}", $s1Cells);
            $sheet->setCellValue("{$colS1}{$fila}", "=IFERROR(AVERAGE(" . implode(',', $refs) . "),0)");
        } else {
            $sheet->setCellValue("{$colS1}{$fila}", "-");
        }
        $sheet->getStyle("{$colS1}{$fila}")->getNumberFormat()->setFormatCode('0%');

        $colS2 = Coordinate::stringFromColumnIndex($col++);
        if(count($s2Cells)) {
            // Build cell references for current row using stored column letters
            $refs = array_map(fn($c) => "{$c}{$fila}", $s2Cells);
            $sheet->setCellValue("{$colS2}{$fila}", "=IFERROR(AVERAGE(" . implode(',', $refs) . "),0)");
        } else {
            $sheet->setCellValue("{$colS2}{$fila}", "-");
        }
        $sheet->getStyle("{$colS2}{$fila}")->getNumberFormat()->setFormatCode('0%');

        $colAnual = Coordinate::stringFromColumnIndex($col++);
        $allS = array_merge($s1Cells, $s2Cells);
        if(count($allS)) {
            // Build cell references for current row using stored column letters
            $refs = array_map(fn($c) => "{$c}{$fila}", $allS);
            $sheet->setCellValue("{$colAnual}{$fila}", "=IFERROR(AVERAGE(" . implode(',', $refs) . "),0)");
        } else {
            $sheet->setCellValue("{$colAnual}{$fila}", "-");
        }
        $sheet->getStyle("{$colAnual}{$fila}")->getNumberFormat()->setFormatCode('0%');

        $colRec = Coordinate::stringFromColumnIndex($col);
        $sheet->setCellValue("{$colRec}{$fila}", $actividad->costo_estimado ?? 0);
        $sheet->getStyle("{$colRec}{$fila}")->getNumberFormat()->setFormatCode('"$" #,##0.00');

        // Bordes finales a la fila
        $sheet->getStyle("B{$fila}:" . Coordinate::stringFromColumnIndex($col) . "{$fila}")->applyFromArray($borderThin);
    }
}