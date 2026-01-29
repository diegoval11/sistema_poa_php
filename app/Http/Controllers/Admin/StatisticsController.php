<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Data for "Cumplimiento por Unidad" (Bar Charts) - All Units
        // We need all units for the ranking, so we get them all but only select necessary fields
        $allUnits = User::where('role', 'unidad')
            ->with(['unidad', 'proyectos' => function($q) {
                $q->where('estado', 'APROBADO')
                  ->with('metas.actividades.programaciones');
            }])
            ->get();

        $statsData = $allUnits->map(function($user) {
            $totalProgramado = 0;
            $totalEjecutado = 0;
            
            foreach ($user->proyectos as $proyecto) {
                foreach ($proyecto->metas as $meta) {
                    foreach ($meta->actividades as $actividad) {
                        foreach ($actividad->programaciones as $prog) {
                            $p = (float)$prog->cantidad_programada;
                            $e = (float)$prog->cantidad_ejecutada;
                            
                            if ($p > 0) {
                                $totalProgramado += $p;
                                $totalEjecutado += min($p, $e); // Cap at 100% per month
                            }
                        }
                    }
                }
            }

            $cumplimiento = $totalProgramado > 0 ? round(($totalEjecutado / $totalProgramado) * 100, 2) : 0;

            return [
                'id' => $user->id,
                'nombre' => $user->unidad->nombre ?? $user->name,
                'cumplimiento' => $cumplimiento
            ];
        });

        // Sort by compliance
        $sortedStats = $statsData->sortByDesc('cumplimiento')->values();

        // Split into Top and Bottom
        $midPoint = ceil($sortedStats->count() / 2);
        $topUnits = $sortedStats->take($midPoint);
        $bottomUnits = $sortedStats->slice($midPoint);

        // 2. Data for "Estado de Proyectos" (Pie Chart)
        $proyectosStatus = \App\Models\PoaProyecto::selectRaw('estado, count(*) as count')
            ->groupBy('estado')
            ->pluck('count', 'estado')
            ->toArray();
            
        // Ensure all statuses exist
        $statuses = ['APROBADO', 'ENVIADO', 'BORRADOR', 'RECHAZADO'];
        $proyectosData = [];
        foreach ($statuses as $status) {
            $proyectosData[] = $proyectosStatus[$status] ?? 0;
        }

        // 3. Data for "Avances Mensuales" (Line Chart) - Global
        $avancesMensuales = [];
        for ($m = 1; $m <= 12; $m++) {
            $progMonthly = \App\Models\PoaProgramacion::where('mes', $m)
                ->whereHas('actividad.meta.proyecto', function($q) {
                    $q->where('estado', 'APROBADO');
                })
                ->sum('cantidad_programada');
                
            // For executed, we need to sum but capping each record is hard in SQL without raw queries or iterating.
            // For global stats, summing raw executed might be okay, but ideally we cap.
            // Let's use a raw query for efficiency or iterate if dataset is small. 
            // Given the complexity, let's try to do it via collection on the fetched programmings if not too heavy.
            // Actually, for the global chart, let's just sum executed. Capping per activity is "correct" but expensive here.
            // Let's stick to simple sum for now to avoid N+1 on all programmings.
            $execMonthly = \App\Models\PoaProgramacion::where('mes', $m)
                ->whereHas('actividad.meta.proyecto', function($q) {
                    $q->where('estado', 'APROBADO');
                })
                ->sum('cantidad_ejecutada');
                
            $avancesMensuales[] = $progMonthly > 0 ? round(($execMonthly / $progMonthly) * 100, 2) : 0;
        }

        // 4. Data for "Trimestral" (Paginated List)
        // We paginate the users first
        $query = User::where('role', 'unidad')->with('unidad');
        
        if ($request->has('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function($q) use ($busqueda) {
                $q->whereHas('unidad', function($subQ) use ($busqueda) {
                    $subQ->where('nombre', 'like', '%' . $busqueda . '%');
                })
                ->orWhere('email', 'like', '%' . $busqueda . '%');
            });
        }
        
        $unidadesTrimestral = $query->paginate(10)->withQueryString();
        
        // Calculate quarters for the paginated units
        $unidadesTrimestral->getCollection()->transform(function($user) {
            $quarters = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            
            // Eager load only for these units
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

        return view('admin.statistics.index', [
            'titulo' => 'Estadísticas de Cumplimiento',
            'topUnits' => $topUnits,
            'bottomUnits' => $bottomUnits,
            'proyectosData' => $proyectosData,
            'avancesMensuales' => $avancesMensuales,
            'unidadesTrimestral' => $unidadesTrimestral,
            'busqueda' => $request->buscar ?? ''
        ]);
    }
}
