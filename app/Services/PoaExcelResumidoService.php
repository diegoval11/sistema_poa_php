<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Service for generating summarized POA Excel reports
 * Shows only PROGRAMADO for each month (ENE-DIC) + RECURSOS
 */
class PoaExcelResumidoService
{
    protected $spreadsheet;
    protected $sheet;
    protected $templateRowPlanificadas = 14;
    protected $templateRowNoPlanificadas = 16;
    protected $currentRow;

    /**
     * Genera el Excel resumido del POA
     */
    public function generarExcelResumido($unidad, $proyectos, $proyectoNoPlanificado, $objetivoEstrategico, $anio)
    {
        // Load the summarized template
        $templatePath = storage_path('app/plantilla/Resumen.xlsx');
        $this->spreadsheet = IOFactory::load($templatePath);
        $this->sheet = $this->spreadsheet->getActiveSheet();
        
        // Update header information
        $this->llenarEncabezados($unidad, $objetivoEstrategico, $anio);
        
        // Process planned activities
        $this->currentRow = $this->templateRowPlanificadas + 1;
        $startRowPlanificadas = $this->currentRow;
        
        $this->llenarActividadesPlanificadas($proyectos);
        
        $endRowPlanificadas = $this->currentRow;
        $totalPlanificadas = $endRowPlanificadas - $startRowPlanificadas;
        
        // Calculate position for unplanned template
        $currentUnplanTemplate = $this->templateRowNoPlanificadas + $totalPlanificadas;
        
        // Process unplanned activities if they exist
        if ($proyectoNoPlanificado) {
            $this->currentRow = $currentUnplanTemplate + 1;
            $this->llenarActividadesNoPlanificadas($proyectoNoPlanificado, $currentUnplanTemplate);
        }
        
        // Remove template rows
        $this->sheet->removeRow($this->templateRowPlanificadas, 1);
        
        // Adjust and remove unplanned template
        $realUnplanTemplatePos = $currentUnplanTemplate - 1;
        $this->sheet->removeRow($realUnplanTemplatePos, 1);
        
        return $this->spreadsheet;
    }

    /**
     * Llena los encabezados del documento
     */
    protected function llenarEncabezados($unidad, $objetivoEstrategico, $anio)
    {
        // Actualizar año en B2
        $valorB2 = $this->sheet->getCell('B2')->getValue();
        if ($valorB2) {
            $nuevoValor = str_replace('2025', $anio, $valorB2);
            $this->sheet->setCellValue('B2', $nuevoValor);
        }
        
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
     * Llena las actividades planificadas (con merge de Proyecto y Meta)
     */
    protected function llenarActividadesPlanificadas($proyectos)
    {
        $num = 1;
        
        foreach ($proyectos as $proyecto) {
            $filaInicioProy = $this->currentRow;
            
            foreach ($proyecto->metas as $meta) {
                $filaInicioMeta = $this->currentRow;
                
                foreach ($meta->actividades as $actividad) {
                    // Insert new row cloning from template
                    $this->insertarFilaClonada($this->currentRow, $this->templateRowPlanificadas);
                    
                    // Fill activity data
                    $this->llenarFilaActividad($actividad, $num);
                    
                    $num++;
                    $this->currentRow++;
                }
                
                // Merge META column (D)
                if ($this->currentRow > $filaInicioMeta) {
                    $this->sheet->mergeCells("D{$filaInicioMeta}:D" . ($this->currentRow - 1));
                    $this->sheet->setCellValue("D{$filaInicioMeta}", $meta->descripcion);
                    $this->sheet->getStyle("D{$filaInicioMeta}")->getAlignment()->setVertical('center');
                }
            }
            
            // Merge PROYECTO column (C)
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
                // Insert new row
                $this->insertarFilaClonada($this->currentRow, $templateRow);
                
                // Fill activity data
                $this->llenarFilaActividad($actividad, $num);
                
                $num++;
                $this->currentRow++;
            }
            
            // Merge META column
            if ($this->currentRow > $filaInicioMeta) {
                $this->sheet->mergeCells("D{$filaInicioMeta}:D" . ($this->currentRow - 1));
                $this->sheet->setCellValue("D{$filaInicioMeta}", $meta->descripcion);
                $this->sheet->getStyle("D{$filaInicioMeta}")->getAlignment()->setVertical('center');
            }
        }
        
        // Merge PROYECTO column
        if ($this->currentRow > $filaInicioProy) {
            $this->sheet->mergeCells("C{$filaInicioProy}:C" . ($this->currentRow - 1));
            $this->sheet->setCellValue("C{$filaInicioProy}", "ACTIVIDADES NO PLANIFICADAS");
            $this->sheet->getStyle("C{$filaInicioProy}")->getAlignment()->setVertical('center');
        }
    }

    /**
     * Inserta una fila clonada desde el template
     */
    protected function insertarFilaClonada($targetRow, $templateRow)
    {
        // Insert new row
        $this->sheet->insertNewRowBefore($targetRow, 1);
        
        // Copy from template (columns B to S = columns 2 to 19)
        for ($col = 2; $col <= 19; $col++) {
            $sourceCell = Coordinate::stringFromColumnIndex($col) . $templateRow;
            $targetCell = Coordinate::stringFromColumnIndex($col) . $targetRow;
            
            // Copy style
            $this->sheet->duplicateStyle(
                $this->sheet->getStyle($sourceCell),
                $targetCell
            );
            
            // Copy value or formula
            $value = $this->sheet->getCell($sourceCell)->getValue();
            
            if ($value !== null && $value !== '') {
                // If formula, adjust row references
                if (is_string($value) && strpos($value, '=') === 0) {
                    $newFormula = preg_replace(
                        '/([A-Z]+)' . $templateRow . '\b/',
                        '${1}' . $targetRow,
                        $value
                    );
                    $this->sheet->setCellValue($targetCell, $newFormula);
                } else {
                    $this->sheet->setCellValue($targetCell, $value);
                }
            }
        }
    }

    /**
     * Llena una fila con los datos de una actividad
     * Solo muestra PROGRAMADO para cada mes + RECURSOS
     */
    protected function llenarFilaActividad($actividad, $num)
    {
        $fila = $this->currentRow;
        
        // B: Número
        $this->sheet->setCellValue("B{$fila}", $num);
        
        // C: Proyecto (será merged después)
        // D: Meta (será merged después)
        
        // E: Actividad
        $this->sheet->setCellValue("E{$fila}", $actividad->descripcion);
        
        // F: Unidad de Medida
        $this->sheet->setCellValue("F{$fila}", $actividad->unidad_medida);
        
        // G-R: Meses ENE-DIC (solo PROGRAMADO)
        // G=ENE, H=FEB, I=MAR, J=ABR, K=MAY, L=JUN,
        // M=JUL, N=AGO, O=SEP, P=OCT, Q=NOV, R=DIC
        
        $month_columns = [
            1 => 'G',   // ENE
            2 => 'H',   // FEB
            3 => 'I',   // MAR
            4 => 'J',   // ABR
            5 => 'K',   // MAY
            6 => 'L',   // JUN
            7 => 'M',   // JUL
            8 => 'N',   // AGO
            9 => 'O',   // SEP
            10 => 'P',  // OCT
            11 => 'Q',  // NOV
            12 => 'R',  // DIC
        ];
        
        foreach ($month_columns as $mes => $column) {
            $prog = $actividad->programaciones->where('mes', $mes)->first();
            if ($prog && $prog->cantidad_programada > 0) {
                $this->sheet->setCellValue("{$column}{$fila}", $prog->cantidad_programada);
            }
        }
        
        // S: RECURSOS (Costo estimado)
        if ($actividad->costo_estimado) {
            $this->sheet->setCellValue("S{$fila}", $actividad->costo_estimado);
        }
    }

    /**
     * Descarga el archivo Excel
     */
    public function descargar($filename = 'POA_Resumen.xlsx')
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
