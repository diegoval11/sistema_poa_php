<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PoaExcelService
{
    protected $spreadsheet;
    protected $sheet;
    protected $templateRowPlanificadas = 15; // Se ajusta en cleanTemplate
    protected $templateRowNoPlanificadas = 23; // Se ajusta en cleanTemplate
    protected $currentRow;

    /**
     * Genera el Excel del POA usando la plantilla
     */
    public function generarExcel($unidad, $proyectos, $proyectoNoPlanificado, $objetivoEstrategico, $anio)
    {
        // Load the Excel template and prepare the worksheet
        $templatePath = storage_path('app/plantilla/Formulas.xlsx');
        $this->spreadsheet = IOFactory::load($templatePath);
        $this->sheet = $this->spreadsheet->getActiveSheet();
        
        // Clear example data and update header information
        $this->cleanTemplate();
        $this->llenarEncabezados($unidad, $objetivoEstrategico, $anio);
        
        // Process planned activities
        // Start insertion after the template row
        $this->currentRow = $this->templateRowPlanificadas + 1;
        $startRowPlanificadas = $this->currentRow;
        
        $this->llenarActividadesPlanificadas($proyectos);
        
        $endRowPlanificadas = $this->currentRow;
        $totalPlanificadas = $endRowPlanificadas - $startRowPlanificadas;
        
        // Define ranges for summary formulas
        $rangeP_Start = $startRowPlanificadas;
        $rangeP_End = $endRowPlanificadas - 1;
        $rangeU_Start = 0;
        $rangeU_End = 0;
        
        // Process unplanned activities if they exist.
        // The unplanned template position shifts due to the inserted planned activities.
        $currentUnplanTemplate = $this->templateRowNoPlanificadas + $totalPlanificadas;
        
        if ($proyectoNoPlanificado) {
            $this->currentRow = $currentUnplanTemplate + 1;
            $rangeU_Start = $this->currentRow;
            
            $totalUnplannedActivities = $this->llenarActividadesNoPlanificadas($proyectoNoPlanificado, $currentUnplanTemplate);
            
            $rangeU_End = $this->currentRow - 1;
        } else {
            $totalUnplannedActivities = 0;
        }
        
        // Final cleanup: Remove original template rows
        $this->sheet->removeRow($this->templateRowPlanificadas, 1);
        
        // Adjust planned ranges (shifted up by 1)
        $rangeP_Start -= 1;
        $rangeP_End -= 1;
        
        // Remove unplanned template
        $finalUnplanTemplatePos = $currentUnplanTemplate;
        
        // Calculate real position after previous deletion
        $realUnplanTemplatePos = $finalUnplanTemplatePos - 1; 
        $this->sheet->removeRow($realUnplanTemplatePos, 1);
        
        // Adjust unplanned ranges (shifted up by 2 total)
        $rangeU_Start -= 2;
        $rangeU_End -= 2;
        
        // Update summary formulas with the valid ranges
        $this->updateSummaryFormulas($rangeP_Start, $rangeP_End, $rangeU_Start, $rangeU_End, $totalUnplannedActivities);
        
        return $this->spreadsheet;
    }

    /**
     * Limpia las filas de ejemplo de la plantilla y ajusta los punteros
     */
    protected function cleanTemplate()
    {
        // 1. Eliminar filas de ejemplo planificadas (15-21)
        // La fila 14 es el template, 15-21 son ejemplos.
        $this->sheet->removeRow(15, 7);
        
        // 2. Eliminar filas de ejemplo no planificadas
        // Originalmente 24-27. Al borrar 7 filas, bajan a 17-20.
        $this->sheet->removeRow(17, 4);
        
        // Nuevas posiciones de templates
        $this->templateRowPlanificadas = 14;
        $this->templateRowNoPlanificadas = 16;
    }

    /**
     * Llena los encabezados del documento
     */
    protected function llenarEncabezados($unidad, $objetivoEstrategico, $anio)
    {
        // Actualizar año en B2
        $valorB2 = $this->sheet->getCell('B2')->getValue();
        $nuevoValor = str_replace('2025', $anio, $valorB2);
        $this->sheet->setCellValue('B2', $nuevoValor);
        
        // C5: UNIDAD
        $this->sheet->setCellValue('C5', "UNIDAD: " . strtoupper($unidad->unidad->nombre));
        
        // C6: OBJETIVO ESTRATÉGICO
        $descripcionObjetivo = $objetivoEstrategico ? $objetivoEstrategico->description : '';
        $this->sheet->setCellValue('C6', "OBJETIVO ESTRATÉGICO: " . $descripcionObjetivo);
        
        // C7: OBJETIVO DE LA UNIDAD
        $objetivos = "";
        foreach ($unidad->proyectos as $p) {
            foreach ($p->metas as $m) {
                $objetivos .= "- " . $m->descripcion . "\n";
            }
        }
        $this->sheet->setCellValue('C7', "OBJETIVO DE LA UNIDAD:\n" . $objetivos);
    }

    /**
     * Llena las actividades planificadas
     */
    protected function llenarActividadesPlanificadas($proyectos)
    {
        $num = 1;
        
        foreach ($proyectos as $proyecto) {
            $filaInicioProy = $this->currentRow;
            
            foreach ($proyecto->metas as $meta) {
                $filaInicioMeta = $this->currentRow;
                
                foreach ($meta->actividades as $actividad) {
                    // SIEMPRE insertar una nueva fila y clonar del template
                    $this->insertarFilaClonada($this->currentRow, $this->templateRowPlanificadas);
                    
                    // Llenar datos de la actividad
                    $this->llenarFilaActividad($actividad, $num);
                    
                    $num++;
                    $this->currentRow++;
                }
                
                // Mergear columna META
                if ($this->currentRow > $filaInicioMeta) {
                    $this->sheet->mergeCells("D{$filaInicioMeta}:D" . ($this->currentRow - 1));
                    $this->sheet->setCellValue("D{$filaInicioMeta}", $meta->descripcion);
                    $this->sheet->getStyle("D{$filaInicioMeta}")->getAlignment()->setVertical('center');
                }
            }
            
            // Mergear columna PROYECTO
            if ($this->currentRow > $filaInicioProy) {
                $this->sheet->mergeCells("C{$filaInicioProy}:C" . ($this->currentRow - 1));
                $this->sheet->setCellValue("C{$filaInicioProy}", $proyecto->nombre);
                $this->sheet->getStyle("C{$filaInicioProy}")->getAlignment()->setVertical('center');
            }
        }
    }

    /**
     * Llena las actividades no planificadas
     */
    protected function llenarActividadesNoPlanificadas($proyectoNoPlanificado, $templateRow)
    {
        $num = 1;
        $filaInicioProy = $this->currentRow;
        
        foreach ($proyectoNoPlanificado->metas as $meta) {
            $filaInicioMeta = $this->currentRow;
            
            foreach ($meta->actividades as $actividad) {
                // SIEMPRE insertar fila clonada desde el template
                $this->insertarFilaClonada($this->currentRow, $templateRow);
                
                // Override formulas for unplanned activities since 'Programado' is 0.
                // Logic: If Executed > 0, then 100% compliance.
                
                $row = $this->currentRow;
                $map = [
                    'H' => 'I', 'L' => 'M', 'P' => 'Q', // Q1
                    'U' => 'V', 'Y' => 'Z', 'AC' => 'AD', // Q2
                    'AH' => 'AI', 'AL' => 'AM', 'AP' => 'AQ', // Q3
                    'AU' => 'AV', 'AY' => 'AZ', 'BC' => 'BD'  // Q4
                ];
                
                // 1. Mensuales
                foreach ($map as $colReal => $colCumpl) {
                    $this->sheet->setCellValue("{$colCumpl}{$row}", "=IF({$colReal}{$row}>0, 1, 0)");
                }
                
                // 2. Trimestrales, Semestrales, Anual (Checks de > 0)
                // Q1: S -> H, L, P
                $this->sheet->setCellValue("S{$row}", "=IF(SUM(H{$row},L{$row},P{$row})>0, 1, 0)");
                // Q2: AF -> U, Y, AC
                $this->sheet->setCellValue("AF{$row}", "=IF(SUM(U{$row},Y{$row},AC{$row})>0, 1, 0)");
                // S1: AG -> (Q1 + Q2)
                $this->sheet->setCellValue("AG{$row}", "=IF(SUM(H{$row},L{$row},P{$row},U{$row},Y{$row},AC{$row})>0, 1, 0)");
                // Q3: AT -> AH, AL, AP
                $this->sheet->setCellValue("AT{$row}", "=IF(SUM(AH{$row},AL{$row},AP{$row})>0, 1, 0)");
                // Q4: BG -> AU, AY, BC
                $this->sheet->setCellValue("BG{$row}", "=IF(SUM(AU{$row},AY{$row},BC{$row})>0, 1, 0)");
                // S2: BH -> (Q3 + Q4)
                $this->sheet->setCellValue("BH{$row}", "=IF(SUM(AH{$row},AL{$row},AP{$row},AU{$row},AY{$row},BC{$row})>0, 1, 0)");
                // Anual: BI -> All
                $allCols = implode("{$row},", array_keys($map)) . "{$row}";
                $this->sheet->setCellValue("BI{$row}", "=IF(SUM({$allCols})>0, 1, 0)");
                
                $this->llenarFilaActividad($actividad, $num);
                
                $num++;
                $this->currentRow++;
            }
            
            // Mergear columna META
            if ($this->currentRow > $filaInicioMeta) {
                $this->sheet->mergeCells("D{$filaInicioMeta}:D" . ($this->currentRow - 1));
                $this->sheet->setCellValue("D{$filaInicioMeta}", $meta->descripcion);
                $this->sheet->getStyle("D{$filaInicioMeta}")->getAlignment()->setVertical('center');
            }
        }
        
        // Mergear columna PROYECTO
        if ($this->currentRow > $filaInicioProy) {
            $this->sheet->mergeCells("C{$filaInicioProy}:C" . ($this->currentRow - 1));
            $this->sheet->setCellValue("C{$filaInicioProy}", "ACTIVIDADES NO PLANIFICADAS");
            $this->sheet->getStyle("C{$filaInicioProy}")->getAlignment()->setVertical('center');
        }
        
        return $num - 1;
    }

    /**
     * Inserta una fila clonada desde una fila template específica
     */
    protected function insertarFilaClonada($targetRow, $templateRow)
    {
        // Insertar nueva fila
        $this->sheet->insertNewRowBefore($targetRow, 1);
        
        // Obtener la última columna con datos
        $highestColumn = $this->sheet->getHighestDataColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
        
        // Calcular la fila fuente REAL (si el template estaba abajo, se desplazó)
        // Pero en nuestra lógica, el template siempre está ARRIBA del target (14 vs 15)
        // O justo arriba (16 vs 17)
        // Así que templateRow es seguro.
        
        // Copiar estilos y fórmulas de la fila template
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sourceCell = Coordinate::stringFromColumnIndex($col) . $templateRow;
            $targetCell = Coordinate::stringFromColumnIndex($col) . $targetRow;
            
            // Copiar estilo
            $this->sheet->duplicateStyle(
                $this->sheet->getStyle($sourceCell),
                $targetCell
            );
            
            // Copiar valor o fórmula
            $value = $this->sheet->getCell($sourceCell)->getValue();
            
            if ($value !== null && $value !== '') {
                // Si es fórmula, ajustar referencias de fila
                if (is_string($value) && strpos($value, '=') === 0) {
                    // Reemplazar referencias: I14 -> I15
                    // Usar regex para asegurar reemplazo exacto del número de fila
                    $newFormula = preg_replace(
                        '/([A-Z]+)' . $templateRow . '\b/',
                        '${1}' . $targetRow,
                        $value
                    );
                    $this->sheet->setCellValue($targetCell, $newFormula);
                } else {
                    // Copiar valor normal
                    $this->sheet->setCellValue($targetCell, $value);
                }
            }
        }
    }

    /**
     * Llena una fila con los datos de una actividad
     */
    protected function llenarFilaActividad($actividad, $num)
    {
        $fila = $this->currentRow;
        
        // Columna B: Número
        $this->sheet->setCellValue("B{$fila}", $num);
        
        // Columna E: Actividad
        $this->sheet->setCellValue("E{$fila}", $actividad->descripcion);
        
        // Columna F: Unidad de Medida
        $this->sheet->setCellValue("F{$fila}", $actividad->unidad_medida);
        
        // Columnas G en adelante: Solo llenar valores (Las fórmulas ya se copiaron)
        $col = 7; // Columna G
        
        for ($mes = 1; $mes <= 12; $mes++) {
            $prog = $actividad->programaciones->where('mes', $mes)->first();
            $cantP = $prog ? $prog->cantidad_programada : 0;
            $cantR = $prog ? $prog->cantidad_ejecutada : 0;
            
            $colP = Coordinate::stringFromColumnIndex($col);
            $colR = Coordinate::stringFromColumnIndex($col + 1);
            
            // Programado
            if ($cantP > 0) {
                $this->sheet->setCellValue("{$colP}{$fila}", $cantP);
            }
            
            // Realizado
            if ($cantR > 0) {
                $this->sheet->setCellValue("{$colR}{$fila}", $cantR);
            }
            
            $col += 4; // Siguiente mes
            
            if ($mes == 6) {
                $col += 2; // Saltar Trimestral (Q2) + Semestral (S1)
            } elseif ($mes == 12) {
                // Saltar Trimestral (Q4) + Semestral (S2) + Anual -> Ir a Costo (Recursos)
                $col += 3;
            } elseif ($mes == 3 || $mes == 9) {
                $col++; // Saltar Trimestral
            }
        }
        
        // Columna final: Costo estimado
        $colCosto = Coordinate::stringFromColumnIndex($col);
        if ($actividad->costo_estimado) {
            $this->sheet->setCellValue("{$colCosto}{$fila}", $actividad->costo_estimado);
        }
    }

    /**
     * Descarga el archivo Excel
     */
    public function descargar($filename = 'POA_Export.xlsx')
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }
    /**
     * Actualiza las fórmulas de resumen (Trimestral, Semestral, Anual, Recursos)
     * Aplicando la regla 80/20 si hay actividades no planificadas.
     */
    protected function updateSummaryFormulas($startP, $endP, $startU, $endU, $unplannedCount)
    {
        $percentCols = ['S', 'AF', 'AG', 'AT', 'BG', 'BH', 'BI'];
        $moneyCol = 'BJ';
        $row = 12; // Fila de resumen

        // 1. Columnas de Porcentaje (Promedios)
        // 1. Columnas de Porcentaje (Promedios)
        foreach ($percentCols as $col) {
            if ($startU > 0 && $endU >= $startU && $unplannedCount > 0) {
                // Proportional 80/20 Rule:
                // Planned: Average * 0.8
                // Unplanned: (Annual Completed Count / Total Unplanned) * 0.2
                $formula = "=IFERROR(AVERAGE({$col}{$startP}:{$col}{$endP}),0)*0.8 + (COUNTIF(BI{$startU}:BI{$endU}, \">=1\") / {$unplannedCount})*0.2";
            } else {
                // 100% Planificadas
                $formula = "=IFERROR(AVERAGE({$col}{$startP}:{$col}{$endP}), 0)";
            }
            $this->sheet->setCellValue("{$col}{$row}", $formula);
        }

        // 2. Columna de Recursos (Suma)
        if ($startU > 0 && $endU > 0) {
            $formulaMoney = "=IFERROR(SUM({$moneyCol}{$startP}:{$moneyCol}{$endP}) + SUM({$moneyCol}{$startU}:{$moneyCol}{$endU}), 0)";
        } else {
            $formulaMoney = "=IFERROR(SUM({$moneyCol}{$startP}:{$moneyCol}{$endP}), 0)";
        }
        $this->sheet->setCellValue("{$moneyCol}{$row}", $formulaMoney);
    }
}
