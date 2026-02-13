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
        
        // Calcular la última fila de datos (ajustado por las 2 eliminaciones de template)
        $lastDataRow = $this->currentRow - 2;
        
        // Configurar columna de Recursos AL FINAL (después de todas las manipulaciones)
        $this->configurarColumnRecursos($lastDataRow);
        
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
     * Configura la columna de Recursos (S) con headers y total
     * Se llama AL FINAL después de todas las manipulaciones de filas
     */
    protected function configurarColumnRecursos($lastDataRow)
    {
        // PASO 1: Limpiar la configuración existente de la plantilla
        // La plantilla tiene S10:S13 merged con "RECURSOS", necesitamos deshacer eso
        
        // Unmerge cualquier celda existente en la columna S (filas 10-13)
        if ($this->sheet->getCell('S10')->isInMergeRange()) {
            $this->sheet->unmergeCells('S10:S13');
        }
        
        // Limpiar valores y estilos existentes en S10-S13
        for ($row = 10; $row <= 13; $row++) {
            $this->sheet->setCellValue("S{$row}", '');
            $this->sheet->getStyle("S{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
            $this->sheet->getStyle("S{$row}")->getFont()->setBold(false)->setSize(11);
        }
        
        // PASO 2: Aplicar nueva configuración
        
        // Estilo de bordes
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        
        // Configurar alturas de filas
        $this->sheet->getRowDimension(10)->setRowHeight(20); // Celda pequeña para total
        $this->sheet->getRowDimension(11)->setRowHeight(20); // Celda pequeña para total
        
        // S10: Primera celda de Total (Azul celeste) - PEQUEÑA
        $this->sheet->setCellValue('S10', "=SUM(S14:S{$lastDataRow})");
        $this->sheet->getStyle('S10')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBDD7EE'); // Azul celeste
        $this->sheet->getStyle('S10')->getNumberFormat()->setFormatCode('"$" #,##0.00');
        $this->sheet->getStyle('S10')->getFont()->setBold(true)->setSize(10);
        $this->sheet->getStyle('S10')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $this->sheet->getStyle('S10')->applyFromArray($borderStyle);
        
        // S11: Segunda celda de Total (Azul celeste) - PEQUEÑA
        $this->sheet->setCellValue('S11', '=S10');
        $this->sheet->getStyle('S11')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBDD7EE'); // Azul celeste
        $this->sheet->getStyle('S11')->getNumberFormat()->setFormatCode('"$" #,##0.00');
        $this->sheet->getStyle('S11')->getFont()->setBold(true)->setSize(10);
        $this->sheet->getStyle('S11')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $this->sheet->getStyle('S11')->applyFromArray($borderStyle);
            
        // S12:S13: Encabezado "10. Total de Recursos $$$" (Verde/Amarillo) - GRANDE
        $this->sheet->mergeCells('S12:S13');
        $this->sheet->setCellValue('S12', "10. Total de\nRecursos\n\$\$\$");
        
        $this->sheet->getStyle('S12:S13')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD8E4BC'); // Verde/Amarillo
            
        $this->sheet->getStyle('S12:S13')->getFont()->setBold(true)->setSize(11);
        $this->sheet->getStyle('S12:S13')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $this->sheet->getStyle('S12:S13')->applyFromArray($borderStyle);
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
            // Agregar formato de moneda con prefijo $
            $this->sheet->getStyle("S{$fila}")->getNumberFormat()->setFormatCode('"$" #,##0.00');
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
