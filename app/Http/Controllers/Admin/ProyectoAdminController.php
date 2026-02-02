<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PoaProyecto;
use App\Models\PoaActividad;
use App\Models\PoaEvidencia;
use App\Exports\ProyectoDetalladoExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ProyectoAdminController extends Controller
{
    /**
     * Muestra el detalle completo de un proyecto para el admin
     */
    public function detalle($proyectoId)
    {
        $proyecto = PoaProyecto::with([
            'unidad.unidad',
            'metas.actividades.programaciones',
            'metas.actividades.evidencias'
        ])->findOrFail($proyectoId);
        
        // Compute project metrics for the view
        $total_actividades = PoaActividad::whereHas('meta', function($q) use ($proyectoId) {
            $q->where('poa_proyecto_id', $proyectoId);
        })->count();
        
        $presupuesto_total = PoaActividad::whereHas('meta', function($q) use ($proyectoId) {
            $q->where('poa_proyecto_id', $proyectoId);
        })->sum('costo_estimado');
        
        $total_evidencias = PoaEvidencia::whereHas('actividad.meta', function($q) use ($proyectoId) {
            $q->where('poa_proyecto_id', $proyectoId);
        })->count();
        
        return view('admin.unidades.detalle_proyecto', [
            'titulo' => 'Detalle: ' . ($proyecto->nombre ?? '(Sin nombre)'),
            'proyecto' => $proyecto,
            'total_actividades' => $total_actividades,
            'presupuesto_total' => $presupuesto_total,
            'total_evidencias' => $total_evidencias,
        ]);
    }
    
    /**
     * Aprueba un proyecto enviado por una unidad
     */
    public function aprobar(Request $request, $proyectoId)
    {
        $proyecto = PoaProyecto::findOrFail($proyectoId);
        
        // Ensure the project is in the correct state before approval to prevent race conditions or duplicate processing.
        abort_if($proyecto->estado !== 'ENVIADO', 400, 'El proyecto ya fue procesado');
        
        $proyecto->estado = 'APROBADO';
        $proyecto->aprobado_por = Auth::id();
        $proyecto->fecha_aprobacion = now();
        $proyecto->save();
        
        // Invalidate cache to ensure the Dashboard reflects the new project status immediately.
        \Illuminate\Support\Facades\Cache::flush();
        
        return redirect()
            ->route('admin.unidades.proyectos', $proyecto->user_id)
            ->with('success', 'El proyecto "' . $proyecto->nombre . '" ha sido aprobado exitosamente.');
    }
    
    /**
     * Rechaza un proyecto enviado por una unidad
     */
    public function rechazar(Request $request, $proyectoId)
    {
        $proyecto = PoaProyecto::findOrFail($proyectoId);
        
        // Ensure the project is in the correct state before rejection.
        abort_if($proyecto->estado !== 'ENVIADO', 400, 'El proyecto ya fue procesado');
        
        // Validate motivo_rechazo is required
        $request->validate([
            'motivo_rechazo' => 'required|string|min:10',
        ], [
            'motivo_rechazo.required' => 'Debes proporcionar un motivo de rechazo.',
            'motivo_rechazo.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);
        
        $proyecto->estado = 'RECHAZADO';
        $proyecto->motivo_rechazo = $request->motivo_rechazo;
        $proyecto->save();
        
        // Invalidate cache to reflect changes.
        \Illuminate\Support\Facades\Cache::flush();
        
        return redirect()
            ->route('admin.unidades.proyectos', $proyecto->user_id)
            ->with('success', 'Proyecto "' . $proyecto->nombre . '" rechazado.');
    }
    
    /**
     * Export proyecto detallado a Excel
     */
    public function exportExcel($proyectoId)
    {
        $proyecto = PoaProyecto::with([
            'unidad.unidad',
            'metas.actividades.programaciones',
            'metas.actividades.evidencias'
        ])->findOrFail($proyectoId);
        
        $fechaGeneracion = now()->format('d/m/Y H:i');
        $fileName = 'POA_Detallado_' . str_replace(' ', '_', $proyecto->unidad->unidad->nombre) . '_' . $proyecto->anio . '_' . now()->format('Ymd') . '.xlsx';
        
        return Excel::download(
            new ProyectoDetalladoExport($proyecto, $fechaGeneracion),
            $fileName
        );
    }
    
    /**
     * Export proyecto detallado a PDF
     */
    public function exportPdf($proyectoId)
    {
        $proyecto = PoaProyecto::with([
            'unidad.unidad',
            'metas.actividades.programaciones',
            'metas.actividades.evidencias'
        ])->findOrFail($proyectoId);
        
        $fechaGeneracion = now()->format('d/m/Y H:i');
        $fileName = 'POA_Detallado_' . str_replace(' ', '_', $proyecto->unidad->unidad->nombre) . '_' . $proyecto->anio . '_' . now()->format('Ymd') . '.pdf';
        
        // Aggregate project statistics for the PDF report
        $totalMetas = $proyecto->metas->count();
        $totalActividades = $proyecto->metas->sum(function($meta) {
            return $meta->actividades->count();
        });
        $presupuestoTotal = $proyecto->metas->sum(function($meta) {
            return $meta->actividades->sum('costo_estimado');
        });
        $totalEvidencias = $proyecto->metas->sum(function($meta) {
            return $meta->actividades->sum(function($actividad) {
                return $actividad->evidencias->count();
            });
        });
        
        $pdf = Pdf::loadView('admin.proyectos.proyecto_detallado', [
            'proyecto' => $proyecto,
            'fechaGeneracion' => $fechaGeneracion,
            'totalMetas' => $totalMetas,
            'totalActividades' => $totalActividades,
            'presupuestoTotal' => $presupuestoTotal,
            'totalEvidencias' => $totalEvidencias,
        ])->setPaper('letter');
        
        return $pdf->download($fileName);
    }
}
