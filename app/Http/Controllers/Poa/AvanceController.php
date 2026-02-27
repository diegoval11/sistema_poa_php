<?php

namespace App\Http\Controllers\Poa;

use App\Http\Controllers\Controller;
use App\Models\PoaProyecto;
use App\Models\PoaProgramacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AvanceController extends Controller
{
    public function index($proyectoId)
    {
        $proyecto = PoaProyecto::with(['metas.actividades.programaciones' => function($query) {
            $query->orderBy('mes');
        }, 'metas.actividades.evidencias'])
        ->where('user_id', Auth::id())
        ->findOrFail($proyectoId);

        // Estadísticas Mensuales y Globales
        $statsMensuales = [];
        $totalProgramadoGlobal = 0;
        $totalEjecutadoEfectivoGlobal = 0;

        for ($m = 1; $m <= 12; $m++) {
            $statsMensuales[$m] = [
                'programado' => 0,
                'ejecutado_efectivo' => 0,
                'porcentaje' => 0
            ];
        }

        foreach ($proyecto->metas as $meta) {
            foreach ($meta->actividades as $actividad) {
                $totalActividadProg = 0;
                $totalActividadEjecEfectivo = 0;

                foreach ($actividad->programaciones as $prog) {
                    $p = (float)$prog->cantidad_programada;
                    $e = (float)$prog->cantidad_ejecutada;
                    $efectivo = min($p, $e);

                    $statsMensuales[$prog->mes]['programado'] += $p;
                    $statsMensuales[$prog->mes]['ejecutado_efectivo'] += $efectivo;

                    $totalActividadProg += $p;
                    $totalActividadEjecEfectivo += $efectivo;
                }

                // Lógica de Cumplimiento por Actividad
                if ($actividad->es_no_planificada) {
                    // Regla especial: 1 realizado = 100%
                    $totalReal = $actividad->programaciones->sum('cantidad_ejecutada');
                    $actividad->porcentaje_cumplimiento = ($totalReal > 0) ? 100 : 0;
                    
                    // NO se toma en cuenta para el global (según requerimiento)
                } else {
                    $actividad->porcentaje_cumplimiento = ($totalActividadProg > 0) 
                        ? round(($totalActividadEjecEfectivo / $totalActividadProg) * 100, 1) 
                        : 0;
                    
                    $totalProgramadoGlobal += $totalActividadProg;
                    $totalEjecutadoEfectivoGlobal += $totalActividadEjecEfectivo;
                }
            }
        }

        foreach ($statsMensuales as $mes => &$data) {
            $data['porcentaje'] = ($data['programado'] > 0) 
                ? round(($data['ejecutado_efectivo'] / $data['programado']) * 100, 1) 
                : 0;
        }

        $porcentajeGlobal = ($totalProgramadoGlobal > 0) 
            ? round(($totalEjecutadoEfectivoGlobal / $totalProgramadoGlobal) * 100, 1) 
            : 0;

        return view('poa.avances.index', compact('proyecto', 'statsMensuales', 'porcentajeGlobal'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'programacion_id' => 'required|exists:poa_programaciones,id',
            'cantidad_ejecutada' => 'required|integer|min:0|max:999999',
        ]);

        $programacion = PoaProgramacion::with('actividad.meta.proyecto')->findOrFail($request->programacion_id);

        if ($programacion->actividad->meta->proyecto->user_id !== Auth::id()) {
            abort(403);
        }

        // Para actividades planificadas (programado > 0), el realizado no puede superar el programado.
        // Las actividades no planificadas (programado == 0) están exentas de esta restricción.
        $esPlanificada = !$programacion->actividad->es_no_planificada;
        if ($esPlanificada && $programacion->cantidad_programada > 0
            && (int)$request->cantidad_ejecutada > $programacion->cantidad_programada) {
            return back()->withErrors([
                'cantidad_ejecutada' => "El realizado ({$request->cantidad_ejecutada}) no puede superar el programado ({$programacion->cantidad_programada}).",
            ])->withInput();
        }

        $programacion->update([
            'cantidad_ejecutada' => (int)$request->cantidad_ejecutada
        ]);

        return back()->with('success', 'Avance actualizado correctamente.');
    }

    public function storeEvidencia(Request $request)
    {
        $request->validate([
            'actividad_id' => 'required|exists:poa_actividades,id',
            'mes' => 'required|integer|min:1|max:12',
            'tipo' => 'required|string|in:PDF,FOTO,VIDEO,URL,MP3',
            'archivo' => 'nullable|file|max:30720', // 30MB
            'url' => 'nullable|url|max:500',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $actividad = \App\Models\PoaActividad::with('meta.proyecto.unidad.unidad')->findOrFail($request->actividad_id);

        if ($actividad->meta->proyecto->user_id !== Auth::id()) {
            abort(403);
        }

        $path = null;
        if ($request->hasFile('archivo')) {
            $anio = date('Y');
            // Obtener el nombre de la unidad y convertirlo a un slug seguro para carpetas
            $nombreUnidad = $actividad->meta->proyecto->unidad->unidad->nombre ?? 'general';
            $carpetaUnidad = \Illuminate\Support\Str::slug($nombreUnidad);
            
            $path = $request->file('archivo')->store("evidencias/{$anio}/{$carpetaUnidad}", 'public');
        }

        \App\Models\PoaEvidencia::create([
            'poa_actividad_id' => $request->actividad_id,
            'mes' => $request->mes,
            'tipo' => $request->tipo,
            'archivo' => $path,
            'url' => $request->url,
            'descripcion' => $request->descripcion,
        ]);

        return back()->with('success', 'Evidencia subida correctamente.');
    }

    public function getEvidencias($actividadId, $mes)
    {
        $evidencias = \App\Models\PoaEvidencia::where('poa_actividad_id', $actividadId)
            ->where('mes', $mes)
            ->get();

        return response()->json([
            'evidencias' => $evidencias->map(function($ev) {
                return [
                    'id' => $ev->id,
                    'tipo' => $ev->tipo,
                    'descripcion' => $ev->descripcion,
                    'archivo' => $ev->archivo ? asset('storage/' . $ev->archivo) : null,
                    'url' => $ev->url,
                    'fecha_subida' => $ev->created_at->format('d/m/Y H:i')
                ];
            })
        ]);
    }
    public function updateCausal(Request $request)
    {
        $request->validate([
            'programacion_id' => 'required|exists:poa_programaciones,id',
            'causal_desvio' => 'nullable|string|max:1000',
        ]);

        $programacion = PoaProgramacion::with('actividad.meta.proyecto')->findOrFail($request->programacion_id);

        if ($programacion->actividad->meta->proyecto->user_id !== Auth::id()) {
            abort(403);
        }

        $programacion->update([
            'causal_desvio' => $request->causal_desvio
        ]);

        $mensaje = $request->filled('causal_desvio') ? 'Causal guardada correctamente.' : 'Causal de incumplimiento eliminada.';
        return back()->with('success', $mensaje);
    }

    public function destroyEvidencia($id)
    {
        $evidencia = \App\Models\PoaEvidencia::with('actividad.meta.proyecto')->findOrFail($id);

        // Verificar que la evidencia pertenece al usuario autenticado
        if ($evidencia->actividad->meta->proyecto->user_id !== Auth::id()) {
            abort(403);
        }

        // Eliminar el archivo físico del storage si existe
        if ($evidencia->archivo) {
            Storage::disk('public')->delete($evidencia->archivo);
        }

        $evidencia->delete();

        return response()->json(['success' => true, 'message' => 'Evidencia eliminada correctamente.']);
    }
}
