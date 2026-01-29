<?php

namespace App\Http\Controllers\Poa;

use App\Http\Controllers\Controller;
use App\Models\PoaActividad;
use App\Models\PoaProyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActividadNoPlanificadaController extends Controller
{
    public function create()
    {
        $anio = date('Y');
        $nombreProyecto = 'ACTIVIDADES NO PLANIFICADAS';

        // 1. Buscar o Crear el Proyecto Contenedor "Invisible"
        $proyecto = PoaProyecto::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'anio' => $anio,
                'nombre' => $nombreProyecto
            ],
            [
                'objetivo_unidad' => 'Contenedor para actividades emergentes no contempladas en el POA inicial.',
                'estado' => 'APROBADO', // Nace aprobado para permitir gestión inmediata
                'fecha_aprobacion' => now()
            ]
        );

        // 2. Buscar o Crear la Meta Especial
        $nombreMeta = 'ACTIVIDADES EMERGENTES ' . $anio;
        
        $meta = \App\Models\PoaMeta::firstOrCreate(
            [
                'poa_proyecto_id' => $proyecto->id,
                'descripcion' => $nombreMeta
            ]
        );

        // Cargar actividades de esta meta específica
        $meta->load('actividades');

        return view('poa.actividad-no-planificada-form', compact('proyecto', 'meta'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:500',
            'unidad_medida' => 'required|string|max:100',
            // Cantidad ya no se requiere - siempre será 0
            'medio_verificacion' => 'required|string|max:255',
            'costo_estimado' => 'nullable|numeric|min:0',
            'recursos' => 'nullable|string|max:500',
        ]);

        $anio = date('Y');
        $nombreProyecto = 'ACTIVIDADES NO PLANIFICADAS';

        // Recuperar el proyecto contenedor
        $proyecto = PoaProyecto::where('user_id', Auth::id())
            ->where('anio', $anio)
            ->where('nombre', $nombreProyecto)
            ->firstOrFail();

        $nombreMeta = 'ACTIVIDADES EMERGENTES ' . $anio;
        $meta = \App\Models\PoaMeta::where('poa_proyecto_id', $proyecto->id)
            ->where('descripcion', $nombreMeta)
            ->firstOrFail();

        DB::transaction(function () use ($request, $meta) {
            PoaActividad::create([
                'poa_meta_id' => $meta->id,
                'descripcion' => $request->descripcion,
                'unidad_medida' => $request->unidad_medida,
                'es_cuantificable' => $request->has('es_cuantificable'),
                // Las actividades no planificadas siempre tienen cantidad 0 (no requieren programación)
                'cantidad_programada_total' => 0,
                'medio_verificacion' => $request->medio_verificacion,
                'costo_estimado' => $request->input('costo_estimado', 0),
                'recursos' => $request->recursos,
                
                // Flags específicos - ahora se crea directamente como APROBADA
                'es_no_planificada' => true,
                'estado_aprobacion' => 'APROBADO'  // Cambiado de PENDIENTE a APROBADO
            ]);
        });

        return redirect()->route('poa.no_planificadas.create')
            ->with('success', 'Actividad No Planificada creada y aprobada automáticamente.');
    }

    public function destroy($id)
    {
        $actividad = PoaActividad::with('meta.proyecto')->findOrFail($id);

        // Verificar que la actividad pertenece al usuario autenticado
        if ($actividad->meta->proyecto->user_id !== Auth::id()) {
            abort(403);
        }

        // Verificar que es una actividad no planificada
        if (!$actividad->es_no_planificada) {
            return back()->with('error', 'Solo se pueden eliminar actividades no planificadas desde aquí.');
        }

        $actividad->delete();

        return back()->with('success', 'Actividad eliminada correctamente.');
    }
}
