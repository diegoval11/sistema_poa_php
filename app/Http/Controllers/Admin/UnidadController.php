<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PoaProyecto;
use App\Models\ObjetivoEspecificoPredeterminado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnidadController extends Controller
{
    /**
     * Lista todas las unidades con buscador dinámico y cálculo de rendimiento
     */
    public function index(Request $request)
    {
        $busqueda = $request->get('buscar', '');
        
        // Eager load relationships with depth to optimize performance and avoid N+1 issues when iterating projects.
        // whereHas('unidad') ensures only users with a complete unidad record are listed,
        // preventing ErrorException when the relation is null (e.g. users created without going through the registration flow).
        $query = User::where('role', 'unidad')
            ->whereHas('unidad')
            ->with(['unidad', 'proyectos' => function($q) {
                $q->where('estado', 'APROBADO')
                  ->with('metas.actividades.programaciones');
            }]);
        
        // Apply search filter
        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->whereHas('unidad', function($subQ) use ($busqueda) {
                    $subQ->where('nombre', 'like', '%' . $busqueda . '%');
                })
                ->orWhere('email', 'like', '%' . $busqueda . '%');
            });
        }
        
        // Load aggregate counts for projects to display summary statistics without loading all models.
        $query->withCount([
            'proyectos as total_proyectos',
            'proyectos as count_proyectos_aprobados' => function($q) {
                $q->where('estado', 'APROBADO');
            }
        ]);
        
        $unidades = $query->paginate(15);
        
        // Calculate performance metrics based on activity execution vs programming.
        // This logic aggregates percentages at the activity level before averaging for the unit.
        foreach ($unidades as $user) {
            $sumaCumplimientos = 0;
            $countActividades = 0;
            
            foreach ($user->proyectos as $proyecto) {
                foreach ($proyecto->metas as $meta) {
                    foreach ($meta->actividades as $actividad) {
                        // Filter valid activities for performance calculation
                        if ($actividad->es_no_planificada || !$actividad->es_cuantificable) {
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
            
            $user->rendimiento = $countActividades > 0 ? round($sumaCumplimientos / $countActividades, 2) : 0;
        }
        
        if ($request->ajax()) {
            return view('admin.unidades.partials.list', [
                'unidades' => $unidades,
                'busqueda' => $busqueda,
            ]);
        }

        return view('admin.unidades.lista_unidades', [
            'titulo' => 'Gestión de Unidades',
            'unidades' => $unidades,
            'busqueda' => $busqueda,
        ]);
    }
    
    /**
     * Lista todos los proyectos de una unidad específica
     */
    public function proyectos($unidadId)
    {
        $unidad = User::where('role', 'unidad')
            ->with('unidad')
            ->findOrFail($unidadId);
        
        $proyectos = PoaProyecto::where('user_id', $unidadId)
            ->orderBy('anio', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Load objectives for dropdown
        $objetivos = ObjetivoEspecificoPredeterminado::orderBy('description')->get();
        
        return view('admin.unidades.proyectos_unidad', [
            'titulo' => 'Proyectos de ' . $unidad->unidad->nombre,
            'unidad' => $unidad,
            'proyectos' => $proyectos,
            'objetivos' => $objetivos,
        ]);
    }
    
    /**
     * Exporta el POA de una unidad a Excel usando plantilla inteligente
     */
    public function exportPoaExcel($unidadId, Request $request)
    {
        // validated objetivo estratégico is optional
        $request->validate([
            'objetivo_estrategico_id' => 'nullable|exists:objetivos_especificos_predeterminados,id'
        ]);
        
        $unidad = User::where('role', 'unidad')
            ->with('unidad')
            ->findOrFail($unidadId);
        
        // Get objective if provided
        $objetivoEstrategico = $request->objetivo_estrategico_id 
            ? ObjetivoEspecificoPredeterminado::find($request->objetivo_estrategico_id) 
            : null;
        
        $anioActual = now()->year;
        
        // Load approved projects (excluding unplanned activities)
        $proyectos = PoaProyecto::with([
            'metas.actividades' => function($q) {
                $q->orderBy('id');
            },
            'metas.actividades.programaciones' => function($q) {
                $q->orderBy('mes');
            },
            'metas.actividades.evidencias'
        ])
        ->where('user_id', $unidadId)
        ->where('estado', 'APROBADO')
        ->where('anio', $anioActual)
        ->where(function($q) {
            $q->where('nombre', '!=', 'ACTIVIDADES NO PLANIFICADAS')
              ->orWhereNull('nombre');
        })
        ->get();
        
        // Load unplanned activities project separately
        $proyectoNoPlanificado = PoaProyecto::with([
            'metas.actividades' => function($q) {
                $q->orderBy('id');
            },
            'metas.actividades.programaciones' => function($q) {
                $q->orderBy('mes');
            },
            'metas.actividades.evidencias'
        ])
        ->where('user_id', $unidadId)
        ->where('estado', 'APROBADO')
        ->where('nombre', 'ACTIVIDADES NO PLANIFICADAS')
        ->where('anio', $anioActual)
        ->first();
        
        // Generate filename
        $nombreUnidad = str_replace(' ', '_', $unidad->unidad->nombre);
        $fileName = "POA_{$nombreUnidad}_{$anioActual}.xlsx";
        
        // Use new service to generate Excel from template
        $service = new \App\Services\PoaExcelService();
        $service->generarExcel($unidad, $proyectos, $proyectoNoPlanificado, $objetivoEstrategico, $anioActual);
        $service->descargar($fileName);
    }
    
    /**
     * Exporta el POA resumido de una unidad a Excel
     */
    public function exportPoaExcelResumido($unidadId, Request $request)
    {
        // validated objetivo estratégico is optional
        $request->validate([
            'objetivo_estrategico_id' => 'nullable|exists:objetivos_especificos_predeterminados,id'
        ]);
        
        $unidad = User::where('role', 'unidad')
            ->with('unidad')
            ->findOrFail($unidadId);
        
        // Get objective if provided
        $objetivoEstrategico = $request->objetivo_estrategico_id 
            ? ObjetivoEspecificoPredeterminado::find($request->objetivo_estrategico_id) 
            : null;
        
        $anioActual = now()->year;
        
        // Load approved projects (excluding unplanned activities)
        $proyectos = PoaProyecto::with([
            'metas.actividades' => function($q) {
                $q->orderBy('id');
            },
            'metas.actividades.programaciones' => function($q) {
                $q->orderBy('mes');
            }
        ])
        ->where('user_id', $unidadId)
        ->where('estado', 'APROBADO')
        ->where('anio', $anioActual)
        ->where(function($q) {
            $q->where('nombre', '!=', 'ACTIVIDADES NO PLANIFICADAS')
              ->orWhereNull('nombre');
        })
        ->get();
        
        // Load unplanned activities project separately
        $proyectoNoPlanificado = PoaProyecto::with([
            'metas.actividades' => function($q) {
                $q->orderBy('id');
            },
            'metas.actividades.programaciones' => function($q) {
                $q->orderBy('mes');
            }
        ])
        ->where('user_id', $unidadId)
        ->where('estado', 'APROBADO')
        ->where('nombre', 'ACTIVIDADES NO PLANIFICADAS')
        ->where('anio', $anioActual)
        ->first();
        
        // Generate filename for summarized version
        $nombreUnidad = str_replace(' ', '_', $unidad->unidad->nombre);
        $fileName = "POA_Resumen_{$nombreUnidad}_{$anioActual}.xlsx";
        
        // Use service to generate summarized Excel
        $service = new \App\Services\PoaExcelResumidoService();
        $service->generarExcelResumido($unidad, $proyectos, $proyectoNoPlanificado, $objetivoEstrategico, $anioActual);
        $service->descargar($fileName);
    }
}
