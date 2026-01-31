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
        // 1. Cargar la plantilla
        $templatePath = storage_path('app/plantilla/Formulas.xlsx');
        $this->spreadsheet = IOFactory::load($templatePath);
        $this->sheet = $this->spreadsheet->getActiveSheet();
        
        // 2. Limpiar datos de ejemplo y configurar punteros
        $this->cleanTemplate();
        
        // 3. Llenar encabezados
        $this->llenarEncabezados($unidad, $objetivoEstrategico, $anio);
        
        // 4. Llenar actividades planificadas
        // Iniciamos inserción en fila 15 (después del template en 14)
        $this->currentRow = $this->templateRowPlanificadas + 1;
        $startRowPlanificadas = $this->currentRow;
        
        $this->llenarActividadesPlanificadas($proyectos);
        
        $endRowPlanificadas = $this->currentRow;
        $totalPlanificadas = $endRowPlanificadas - $startRowPlanificadas;
        
        // 5. Llenar actividades no planificadas
        // La template de no planificadas está originalmente en 16.
        // Pero al insertar filas planificadas, se desplazó abajo por $totalPlanificadas.
        $currentUnplanTemplate = $this->templateRowNoPlanificadas + $totalPlanificadas;
        
        if ($proyectoNoPlanificado) {
            // Iniciar inserción después del template
            $this->currentRow = $currentUnplanTemplate + 1;
            $this->llenarActividadesNoPlanificadas($proyectoNoPlanificado, $currentUnplanTemplate);
        }
        
        // 6. Limpiar templates originales
        // Remover fila 14 (template planificadas)
        $this->sheet->removeRow($this->templateRowPlanificadas, 1);
        
        // Remover template no planificadas
        // Ahora está en $currentUnplanTemplate - 1 (porque borramos 1 fila arriba)
        $finalUnplanTemplatePos = $currentUnplanTemplate - 1;
        $this->sheet->removeRow($finalUnplanTemplatePos, 1);
        
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
        $this->sheet->setCellValue('C6', "OBJETIVO ESTRATÉGICO: " . $objetivoEstrategico->description);
        
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
}
