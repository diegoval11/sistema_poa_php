<?php

namespace App\Http\Controllers\Poa;

use App\Http\Controllers\Controller;
use App\Models\PoaProyecto;
use App\Models\PoaMeta;
use App\Models\PoaActividad;
use App\Models\PoaProgramacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
// IMPORTANTE: Esta línea debe estar aquí para que el __construct funcione
use App\Services\PoaWizardValidator;

class WizardController extends Controller
{
    protected $validator;

    public function __construct(PoaWizardValidator $validator)
    {
        $this->validator = $validator;
    }

    // ... (Tus métodos GET step1, step2, step3, step4, step5 siguen igual) ...
    public function step1($id = null)
    {
        $proyecto = $id ? PoaProyecto::where('user_id', Auth::id())->findOrFail($id) : null;
        return view('poa.wizard.step1', compact('proyecto'));
    }

    public function step2($id)
    {
        $proyecto = PoaProyecto::findOrFail($id);
        
        // Cargar metas predeterminadas desde la base de datos
        $metasPredeterminadas = \App\Models\MetaPredeterminada::orderBy('description')->pluck('description')->toArray();
        
        return view('poa.wizard.step2', compact('proyecto', 'metasPredeterminadas'));
    }

    public function step3($id)
    {
        $proyecto = PoaProyecto::with('metas.actividades')->findOrFail($id);
        $check = $this->validator->validateStep3($proyecto);
        if (!$check['allowed']) return redirect()->route($check['route'], $id)->with('error', $check['message']);
        return view('poa.wizard.step3', compact('proyecto'));
    }

    public function step4($id)
    {
        $proyecto = PoaProyecto::with('metas.actividades.programaciones')->findOrFail($id);
        $check = $this->validator->validateStep4($proyecto);
        if (!$check['allowed']) return redirect()->route($check['route'], $id)->with('error', $check['message']);
        return view('poa.wizard.step4', compact('proyecto'));
    }

    public function step5($id)
    {
        $proyecto = PoaProyecto::with(['metas.actividades.programaciones', 'unidad'])->findOrFail($id);
        $check = $this->validator->validateStep5($proyecto);
        if (!$check['allowed']) return redirect()->route($check['route'], $id)->with('error', $check['message']);

        $totalActividades = $proyecto->metas->sum(fn($m) => $m->actividades->count());
        return view('poa.wizard.step5', compact('proyecto', 'totalActividades'));
    }

    // ... (MÉTODOS POST) ...

    public function storeStep1(Request $request, $id = null)
    {
        $request->validate([
            'anio' => 'required|integer|min:2020|max:2100',
            // Validamos que sea nullable (opcional)
            'nombre' => 'nullable|string|max:200',
            'objetivo_unidad' => 'nullable|string|max:1000',
        ]);

        $proyecto = DB::transaction(function () use ($request, $id) {
            return PoaProyecto::updateOrCreate(
                ['id' => $id],
                [
                    'user_id' => Auth::id(),
                    'anio' => $request->anio,

                    // --- CORRECCIÓN ---
                    // Eliminamos el fallback. Si no hay nombre, se guarda NULL.
                    'nombre' => $request->nombre,
                    // ------------------

                    'objetivo_unidad' => $request->objetivo_unidad,

                    // Solo asignamos BORRADOR si estamos creando uno nuevo
                    // Si ya existía (update), no tocamos el estado.
                    'estado' => $id ? DB::raw('estado') : 'BORRADOR'
                ]
            );
        });

        return redirect()->route('poa.wizard.step2', $proyecto->id)
            ->with('success', 'Información guardada correctamente.');
    }

    public function storeMeta(Request $request, $id)
    {
        $request->validate([
            'meta_predeterminada' => 'required_without:descripcion',
            'descripcion' => 'required_if:meta_predeterminada,OTRA|max:500'
        ]);

        DB::transaction(function () use ($request, $id) {
            $descripcion = $request->meta_predeterminada === 'OTRA' ? $request->descripcion : $request->meta_predeterminada;
            PoaMeta::create(['poa_proyecto_id' => $id, 'descripcion' => $descripcion]);
        });

        return redirect()->route('poa.wizard.step2', $id)->with('success', 'Meta agregada.');
    }

    // === AQUÍ ESTABA EL ERROR: Cambiamos 'required' por 'nullable' ===
    public function storeActividad(Request $request, $id)
    {
        $request->validate([
            'poa_meta_id' => 'required|exists:poa_metas,id',
            'descripcion' => 'required|string|max:500',
            'unidad_medida' => 'required|string|max:100',
            'cantidad_programada_total' => 'required_if:es_cuantificable,1|integer|min:1',
            'medio_verificacion' => 'required|string|max:255',

            // CORREGIDO: nullable permite que venga vacío
            'costo_estimado' => 'nullable|numeric|min:0',
            'recursos' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            PoaActividad::create([
                'poa_meta_id' => $request->poa_meta_id,
                'descripcion' => $request->descripcion,
                'unidad_medida' => $request->unidad_medida,
                'es_cuantificable' => $request->has('es_cuantificable'),
                'cantidad_programada_total' => $request->has('es_cuantificable') ? (int)$request->cantidad_programada_total : 0,
                'medio_verificacion' => $request->medio_verificacion,

                // Si viene vacío (null), guardamos 0 para el costo
                'costo_estimado' => $request->input('costo_estimado', 0),
                'recursos' => $request->recursos,
            ]);
        });

        return redirect()->route('poa.wizard.step3', ['id' => $id, 'tab' => $request->poa_meta_id])
            ->with('success', 'Actividad registrada correctamente.');
    }

    public function updateMeta(Request $request, $id)
    {
        $meta = PoaMeta::findOrFail($id);
        $proyecto = $meta->proyecto;
        
        // Validar que el proyecto pertenece al usuario
        if ($proyecto->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }
        
        // Validar que el proyecto está en BORRADOR
        if ($proyecto->estado !== 'BORRADOR') {
            return redirect()->back()->with('error', 'Solo se pueden editar metas de proyectos en estado BORRADOR.');
        }
        
        $request->validate([
            'descripcion' => 'required|string|max:500'
        ]);
        
        DB::transaction(function () use ($meta, $request) {
            $meta->update(['descripcion' => $request->descripcion]);
        });
        
        return redirect()->route('poa.wizard.step2', $proyecto->id)
            ->with('success', 'Meta actualizada correctamente.');
    }

    public function updateActividad(Request $request, $id)
    {
        $actividad = PoaActividad::findOrFail($id);
        $proyecto = $actividad->meta->proyecto;
        
        // Validar que el proyecto pertenece al usuario
        if ($proyecto->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }
        
        // Validar que el proyecto está en BORRADOR
        if ($proyecto->estado !== 'BORRADOR') {
            return redirect()->back()->with('error', 'Solo se pueden editar actividades de proyectos en estado BORRADOR.');
        }
        
        $request->validate([
            'descripcion' => 'required|string|max:500',
            'unidad_medida' => 'required|string|max:100',
            'cantidad_programada_total' => 'required_if:es_cuantificable,1|integer|min:1',
            'medio_verificacion' => 'required|string|max:255',
            'costo_estimado' => 'nullable|numeric|min:0',
            'recursos' => 'nullable|string|max:500',
        ]);
        
        DB::transaction(function () use ($actividad, $request) {
            $actividad->update([
                'descripcion' => $request->descripcion,
                'unidad_medida' => $request->unidad_medida,
                'es_cuantificable' => $request->has('es_cuantificable'),
                'cantidad_programada_total' => $request->has('es_cuantificable') ? (int)$request->cantidad_programada_total : 0,
                'medio_verificacion' => $request->medio_verificacion,
                'costo_estimado' => $request->input('costo_estimado', 0),
                'recursos' => $request->recursos,
            ]);
        });
        
        return redirect()->route('poa.wizard.step3', ['id' => $proyecto->id, 'tab' => $actividad->poa_meta_id])
            ->with('success', 'Actividad actualizada correctamente.');
    }

    public function updateProgramacion(Request $request, $id)
    {
        DB::transaction(function () use ($request) {
            if ($request->has('programacion')) {
                foreach ($request->programacion as $actividadId => $meses) {
                    foreach ($meses as $mes => $cantidad) {
                        // Sanitizar: convertir valores vacíos, null, o no numéricos a 0
                        $cantidadFinal = is_numeric($cantidad) && $cantidad !== '' ? (int)$cantidad : 0;
                        
                        PoaProgramacion::where('poa_actividad_id', $actividadId)
                            ->where('mes', $mes)
                            ->update(['cantidad_programada' => $cantidadFinal]);
                    }
                }
            }
        });

        $proyecto = PoaProyecto::with('metas.actividades.programaciones')->findOrFail($id);

        // Si aquí te da error, asegúrate de haber actualizado App/Services/PoaWizardValidator.php
        // con la función checkDistribution() que te pasé en la respuesta anterior.
        $check = $this->validator->checkDistribution($proyecto);

        if (!$check['valid']) {
            return redirect()->route('poa.wizard.step4', $id)->with('error', $check['message']);
        }

        return redirect()->route('poa.wizard.step5', $id)->with('success', 'Cronograma guardado.');
    }

    public function deleteMeta($id) {
        DB::transaction(function () use ($id) {
            PoaMeta::findOrFail($id)->delete();
        });
        return back()->with('success', 'Meta eliminada.');
    }

    public function deleteActividad($id) {
        DB::transaction(function () use ($id) {
            PoaActividad::findOrFail($id)->delete();
        });
        return back()->with('success', 'Actividad eliminada.');


    }







    public function deleteProject($id) {
        DB::transaction(function () use ($id) {
            PoaProyecto::where('user_id', Auth::id())
                ->whereIn('estado', ['BORRADOR', 'RECHAZADO'])
                ->findOrFail($id)
                ->delete();
        });
        return back()->with('success', 'Proyecto eliminado.');
    }





    public function sendProject($id) {
        DB::transaction(function () use ($id) {
            PoaProyecto::where('user_id', Auth::id())
                ->where('estado', 'BORRADOR')
                ->findOrFail($id)
                ->update(['estado' => 'ENVIADO']);
        });
        return back()->with('success', 'Proyecto enviado.');
    }

    public function finish($id)
    {
        // Cargamos relaciones para validación profunda
        $proyecto = PoaProyecto::with('metas.actividades.programaciones')->findOrFail($id);

        // 1. Usamos el validador inyectado en el __construct
        $check = $this->validator->validateStep5($proyecto);

        if (!$check['allowed']) {
            return redirect()->route($check['route'], $id)->with('error', $check['message']);
        }

        // 2. Redirigimos
        return redirect()->route('dashboard')
            ->with('success', 'Planificación completada correctamente. Tu proyecto está listo para ser enviado a revisión.');
    }
}
