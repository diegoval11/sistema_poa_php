<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ReporteTrimestralExport;
use App\Exports\ReporteUnidadesExport;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function exportTrimestralExcel()
    {
        // Get all units with their quarterly data
        $unidades = $this->getUnidadesConTrimestres();
        
        $fechaGeneracion = now()->format('d/m/Y H:i');
        $usuarioGenerador = auth()->user()->email;
        $fileName = 'Reporte_Trimestral_' . now()->format('Ymd_Hi') . '.xlsx';
        
        return Excel::download(
            new ReporteTrimestralExport($unidades, $fechaGeneracion, $usuarioGenerador),
            $fileName
        );
    }

    public function exportTrimestralPdf()
    {
        // Get all units with their quarterly data
        $unidades = $this->getUnidadesConTrimestres();
        
        $fechaGeneracion = now()->format('d/m/Y H:i');
        $usuarioGenerador = auth()->user()->email;
        $fileName = 'Reporte_Trimestral_' . now()->format('Ymd_Hi') . '.pdf';
        
        $pdf = Pdf::loadView('admin.statistics.reporte_trimestral', [
            'unidades' => $unidades,
            'fechaGeneracion' => $fechaGeneracion,
            'usuarioGenerador' => $usuarioGenerador,
        ]);
        
        return $pdf->download($fileName);
    }

    public function exportUnidadesExcel()
    {
        // Get all units with their performance data
        $data = $this->getUnidadesConRendimiento();
        
        $fechaGeneracion = now()->format('d/m/Y H:i');
        $fileName = 'Reporte_Unidades_' . now()->format('Ymd_Hi') . '.xlsx';
        
        return Excel::download(
            new ReporteUnidadesExport($data['unidades'], $fechaGeneracion, $data['resumen']),
            $fileName
        );
    }

    public function exportUnidadesPdf()
    {
        // Get all units with their performance data
        $data = $this->getUnidadesConRendimiento();
        
        $fechaGeneracion = now()->format('d/m/Y H:i');
        $fileName = 'Reporte_Unidades_' . now()->format('Ymd_Hi') . '.pdf';
        
        $pdf = Pdf::loadView('admin.statistics.reporte_unidades', [
            'unidades' => $data['unidades'],
            'fechaGeneracion' => $fechaGeneracion,
            'resumen' => $data['resumen'],
        ]);
        
        return $pdf->download($fileName);
    }

    /**
     * Get all units with their overall performance and categorization
     * UNIFIED CALCULATION: Group by activity, calculate % per activity, then average
     * Based on Django reference implementation
     */
    private function getUnidadesConRendimiento()
    {
        $unidades = User::where('role', 'unidad')
            ->with(['unidad', 'proyectos' => function($q) {
                $q->where('estado', 'APROBADO')
                  ->with('metas.actividades.programaciones');
            }])
            ->withCount('proyectos')
            ->withCount(['proyectos as count_proyectos_aprobados' => function($q) {
                $q->where('estado', 'APROBADO');
            }])
            ->get();
        
        // Calculate performance for each unit
        $unidades->transform(function($user) {
            $sumaCumplimientos = 0;
            $countActividades = 0;
            
            foreach ($user->proyectos as $proyecto) {
                foreach ($proyecto->metas as $meta) {
                    foreach ($meta->actividades as $actividad) {
                        // CRITICAL: Skip unplanned activities
                        if ($actividad->es_no_planificada) {
                            continue;
                        }
                        
                        // CRITICAL: Only consider quantifiable activities
                        if (!$actividad->es_cuantificable) {
                            continue;
                        }
                        
                        // Group programaciones by activity
                        $actividadProgramado = 0;
                        $actividadEjecutado = 0;
                        
                        foreach ($actividad->programaciones as $prog) {
                            if ($prog->cantidad_programada > 0) {
                                $actividadProgramado += $prog->cantidad_programada;
                                $actividadEjecutado += min($prog->cantidad_programada, $prog->cantidad_ejecutada);
                            }
                        }
                        
                        // Calculate % for this activity
                        if ($actividadProgramado > 0) {
                            $cumplimientoActividad = ($actividadEjecutado / $actividadProgramado) * 100;
                            $sumaCumplimientos += min(100, $cumplimientoActividad);
                            $countActividades++;
                        }
                    }
                }
            }
            
            $rendimiento = $countActividades > 0 ? round($sumaCumplimientos / $countActividades, 2) : 0;
            
            $user->total_proyectos = $user->proyectos_count;
            $user->rendimiento = $rendimiento;
            
            return $user;
        });
        
        // Calculate summary statistics
        $resumen = [
            'total' => $unidades->count(),
            'excelente' => $unidades->filter(fn($u) => $u->rendimiento >= 80)->count(),
            'bueno' => $unidades->filter(fn($u) => $u->rendimiento >= 60 && $u->rendimiento < 80)->count(),
            'regular' => $unidades->filter(fn($u) => $u->rendimiento >= 40 && $u->rendimiento < 60)->count(),
            'bajo' => $unidades->filter(fn($u) => $u->rendimiento < 40)->count(),
        ];
        
        return [
            'unidades' => $unidades,
            'resumen' => $resumen,
        ];
    }

    /**
     * Get all units with their quarterly compliance percentages
     */
    private function getUnidadesConTrimestres()
    {
        $unidades = User::where('role', 'unidad')
            ->with('unidad')
            ->get();
        
        $unidades->transform(function($user) {
            $quarters = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            
            // Eager load projects
            $user->load(['proyectos' => function($q) {
                $q->where('estado', 'APROBADO')->with('metas.actividades.programaciones');
            }]);
            
            $qData = [
                1 => ['p' => 0, 'e' => 0],
                2 => ['p' => 0, 'e' => 0],
                3 => ['p' => 0, 'e' => 0],
                4 => ['p' => 0, 'e' => 0],
            ];
            
            foreach ($user->proyectos as $proyecto) {
                foreach ($proyecto->metas as $meta) {
                    foreach ($meta->actividades as $actividad) {
                        foreach ($actividad->programaciones as $prog) {
                            $mes = $prog->mes;
                            $quarter = ceil($mes / 3);
                            
                            $p = (float)$prog->cantidad_programada;
                            $e = (float)$prog->cantidad_ejecutada;
                            
                            if ($p > 0) {
                                $qData[$quarter]['p'] += $p;
                                $qData[$quarter]['e'] += min($p, $e);
                            }
                        }
                    }
                }
            }
            
            foreach ($qData as $q => $data) {
                $quarters[$q] = $data['p'] > 0 ? round(($data['e'] / $data['p']) * 100, 1) : 0;
            }
            
            $user->quarters = $quarters;
            return $user;
        });
        
        return $unidades;
    }
}
