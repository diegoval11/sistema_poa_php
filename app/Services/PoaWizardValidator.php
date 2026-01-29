<?php

namespace App\Services;

use App\Models\PoaProyecto;

class PoaWizardValidator
{
    /**
     * Valida si el proyecto puede entrar al paso 3 (Actividades).
     */
    public function validateStep3(PoaProyecto $proyecto): array
    {
        if ($proyecto->metas->isEmpty()) {
            return [
                'allowed' => false,
                'message' => 'Acceso denegado: Debes registrar al menos una meta antes de continuar.',
                'route' => 'poa.wizard.step2'
            ];
        }
        return ['allowed' => true];
    }

    public function validateStep4(PoaProyecto $proyecto): array
    {
        // Validación en cascada
        $step3Check = $this->validateStep3($proyecto);
        if (!$step3Check['allowed']) {
            return $step3Check;
        }

        // Regla: Metas sin actividades
        $metasSinActividades = $proyecto->metas->filter(fn($m) => $m->actividades->isEmpty());

        if ($metasSinActividades->isNotEmpty()) {
            return [
                'allowed' => false,
                'message' => 'Acceso denegado: Todas tus metas deben tener actividades asignadas.',
                'route' => 'poa.wizard.step3'
            ];
        }

        return ['allowed' => true];
    }

    public function validateStep5(PoaProyecto $proyecto): array
    {
         // 1. Validar pasos anteriores
         $step4Check = $this->validateStep4($proyecto);
         if (!$step4Check['allowed']) {
             return $step4Check;
         }

         // 2. Validar Distribución Matemática
         $distCheck = $this->checkDistribution($proyecto);

         if (!$distCheck['valid']) {
             return [
                 'allowed' => false,
                 'message' => $distCheck['message'],
                 'route' => 'poa.wizard.step4'
             ];
         }

         return ['allowed' => true];
    }

    /**
     * Valida que la suma de meses coincida con el total anual.
     */
    public function checkDistribution(PoaProyecto $proyecto): array
    {
        foreach ($proyecto->metas as $meta) {
            foreach ($meta->actividades as $actividad) {

                if ($actividad->es_cuantificable) {

                    // CORRECCIÓN: Ahora son enteros, no decimales
                    $totalAnual = (int) $actividad->cantidad_programada_total;
                    $sumaMensual = (int) $actividad->programaciones->sum('cantidad_programada');

                    // Comparación exacta (sin tolerancia decimal)
                    if ($totalAnual !== $sumaMensual) {
                        return [
                            'valid' => false,
                            'message' => "Error de consistencia en: '{$actividad->descripcion}'. Total definido: {$totalAnual} | Suma mensual: {$sumaMensual}. Deben ser iguales."
                        ];
                    }
                }
            }
        }

        return ['valid' => true];
    }




}
