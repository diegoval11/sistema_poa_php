<?php

use App\Http\Controllers\Auth\ForcePasswordChangeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Poa\WizardController;
use App\Http\Controllers\Poa\PoaController;
use Illuminate\Support\Facades\Auth;
use App\Models\PoaProyecto;
Route::redirect('/', '/login');

Route::middleware(['auth'])->group(function () {

    Route::get('/cambiar-clave-obligatoria', [ForcePasswordChangeController::class, 'edit'])
        ->name('password.change.notice');

    Route::post('/cambiar-clave-obligatoria', [ForcePasswordChangeController::class, 'update'])
        ->name('password.change.store');


    Route::middleware(['role:unidad', 'check.password.change'])->group(function () {

        // Esta es la ruta landing de la unidad
        Route::get('/dashboard', function () {

            // Lógica de negocio: Traer POAs de la unidad logueada
            $proyectos = PoaProyecto::where('user_id', Auth::id())
                            ->where(function ($q) {
                                $q->where('nombre', '!=', 'ACTIVIDADES NO PLANIFICADAS')
                                  ->orWhereHas('metas.actividades');
                            })
                            ->with('metas.actividades.programaciones') // Eager load for calculation
                            ->latest('updated_at')
                            ->get(); // Traemos TODOS para las estadísticas

            $proyectosRecientes = $proyectos->take(2); // Solo mostramos 2 en la tabla

            // Calcular Cumplimiento General
            $totalProgramado = 0;
            $totalEjecutado = 0;
            $countActividades = 0;
            $sumaPorcentajes = 0;

            foreach ($proyectos as $proyecto) {
                if ($proyecto->estado === 'APROBADO') {
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
                            
                            $actividadProgramado = 0;
                            $actividadEjecutado = 0;
                            
                            foreach ($actividad->programaciones as $prog) {
                                $p = (float)$prog->cantidad_programada;
                                $e = (float)$prog->cantidad_ejecutada;
                                
                                if ($p > 0) {
                                    $actividadProgramado += $p;
                                    $actividadEjecutado += min($p, $e);
                                }
                            }

                            if ($actividadProgramado > 0) {
                                $cumplimientoActividad = ($actividadEjecutado / $actividadProgramado) * 100;
                                $sumaPorcentajes += min(100, $cumplimientoActividad);
                                $countActividades++;
                            }
                        }
                    }
                }
            }

            $cumplimientoGeneral = $countActividades > 0 ? round($sumaPorcentajes / $countActividades, 2) : 0;

            return view('unidad.dashboard', compact('proyectos', 'proyectosRecientes', 'cumplimientoGeneral'));

        })->name('dashboard');

        // Proyectos
        Route::prefix('poa')->name('poa.')->group(function () {
            Route::get('/mis-proyectos', [PoaController::class, 'index'])->name('lista_proyectos');
        });

        // Rutas para Actividades No Planificadas
        Route::prefix('poa/actividades-no-planificadas')->name('poa.no_planificadas.')->group(function () {
            Route::get('/crear', [\App\Http\Controllers\Poa\ActividadNoPlanificadaController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Poa\ActividadNoPlanificadaController::class, 'store'])->name('store');
            Route::delete('/{id}', [\App\Http\Controllers\Poa\ActividadNoPlanificadaController::class, 'destroy'])->name('destroy');
        });

        // Rutas para Gestionar Avances (Normales)
        Route::prefix('poa/avances')->name('poa.avances.')->group(function () {
            Route::get('/{proyectoId}', [\App\Http\Controllers\Poa\AvanceController::class, 'index'])->name('index');
            Route::post('/update', [\App\Http\Controllers\Poa\AvanceController::class, 'update'])->name('update');
            Route::post('/store-evidencia', [\App\Http\Controllers\Poa\AvanceController::class, 'storeEvidencia'])->name('store_evidencia');
            Route::get('/evidencias-mes/{actividadId}/{mes}', [\App\Http\Controllers\Poa\AvanceController::class, 'getEvidencias'])->name('get_evidencias');
        });

        // Wizard
        Route::prefix('poa/wizard')->name('poa.wizard.')->group(function () {
            Route::post('/{id}/eliminar', [WizardController::class, 'deleteProject'])->name('deleteProject');
            Route::post('/{id}/enviar', [WizardController::class, 'sendProject'])->name('sendProject');
            Route::get('/inicio/{id?}', [WizardController::class, 'step1'])->name('step1');
            Route::post('/inicio/{id?}', [WizardController::class, 'storeStep1'])->name('storeStep1');

            Route::get('/{id}/metas', [WizardController::class, 'step2'])->name('step2');
            Route::post('/{id}/metas', [WizardController::class, 'storeMeta'])->name('storeMeta');
            Route::delete('/eliminar-meta/{id}', [WizardController::class, 'deleteMeta'])->name('deleteMeta');

            Route::get('/{id}/actividades', [WizardController::class, 'step3'])->name('step3');
            Route::post('/{id}/actividades', [WizardController::class, 'storeActividad'])->name('storeActividad');
            Route::delete('/eliminar-actividad/{id}', [WizardController::class, 'deleteActividad'])->name('deleteActividad');

            Route::get('/{id}/programacion', [WizardController::class, 'step4'])->name('step4');
            Route::get('/{id}/resumen', [WizardController::class, 'step5'])->name('step5');

            Route::post('/{id}/programacion', [WizardController::class, 'updateProgramacion'])->name('storeProgramacion');
            Route::post('/{id}/finalizar', [WizardController::class, 'finish'])->name('finish');
        });

    });

    // --- ZONA DE ADMIN

    Route::middleware(['role:admin', 'check.password.change'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Unidades Management
        Route::prefix('unidades')->name('admin.unidades.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UnidadController::class, 'index'])->name('index');
            Route::get('/{id}/proyectos', [\App\Http\Controllers\Admin\UnidadController::class, 'proyectos'])->name('proyectos');
            Route::get('/{id}/exportar-poa', [\App\Http\Controllers\Admin\UnidadController::class, 'exportPoaExcel'])->name('exportar-poa');
        });

        // Estadísticas
        Route::get('/statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('admin.statistics.index');
        
        // Exportaciones
        Route::get('/statistics/export-excel', [\App\Http\Controllers\Admin\ExportController::class, 'exportTrimestralExcel'])->name('admin.statistics.export.excel');
        Route::get('/statistics/export-pdf', [\App\Http\Controllers\Admin\ExportController::class, 'exportTrimestralPdf'])->name('admin.statistics.export.pdf');
        
        Route::get('/unidades/export-excel', [\App\Http\Controllers\Admin\ExportController::class, 'exportUnidadesExcel'])->name('admin.unidades.export.excel');
        Route::get('/unidades/export-pdf', [\App\Http\Controllers\Admin\ExportController::class, 'exportUnidadesPdf'])->name('admin.unidades.export.pdf');

        // Proyectos Admin
        Route::prefix('proyectos')->name('admin.proyectos.')->group(function () {
            Route::get('/{id}', [\App\Http\Controllers\Admin\ProyectoAdminController::class, 'detalle'])->name('detalle');
            Route::post('/{id}/aprobar', [\App\Http\Controllers\Admin\ProyectoAdminController::class, 'aprobar'])->name('aprobar');
            Route::post('/{id}/rechazar', [\App\Http\Controllers\Admin\ProyectoAdminController::class, 'rechazar'])->name('rechazar');
            Route::get('/{id}/export-excel', [\App\Http\Controllers\Admin\ProyectoAdminController::class, 'exportExcel'])->name('export.excel');
            Route::get('/{id}/export-pdf', [\App\Http\Controllers\Admin\ProyectoAdminController::class, 'exportPdf'])->name('export.pdf');
        });

        // Panel Avanzado
        Route::prefix('panel')->name('admin.panel.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminPanelController::class, 'index'])->name('index');
            
            // Proyectos
            Route::get('/proyectos', [\App\Http\Controllers\Admin\AdminPanelController::class, 'proyectos'])->name('proyectos');
            Route::post('/proyectos', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeProyecto'])->name('proyectos.store');
            Route::put('/proyectos/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateProyecto'])->name('proyectos.update');
            Route::delete('/proyectos/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'destroyProyecto'])->name('proyectos.destroy');
            Route::post('/proyectos/{id}/restore', [\App\Http\Controllers\Admin\AdminPanelController::class, 'restoreProyecto'])->name('proyectos.restore');
            Route::delete('/proyectos/trash/empty', [\App\Http\Controllers\Admin\AdminPanelController::class, 'emptyTrash'])->name('proyectos.empty-trash');
            
            // Usuarios
            Route::get('/usuarios', [\App\Http\Controllers\Admin\AdminPanelController::class, 'usuarios'])->name('usuarios');
            Route::post('/usuarios', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeUsuario'])->name('usuarios.store');
            Route::put('/usuarios/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateUsuario'])->name('usuarios.update');
            Route::delete('/usuarios/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'destroyUsuario'])->name('usuarios.destroy');
            
            // Unidades
            Route::get('/unidades', [\App\Http\Controllers\Admin\AdminPanelController::class, 'unidades'])->name('unidades');
            Route::post('/unidades', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeUnidad'])->name('unidades.store');
            Route::put('/unidades/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateUnidad'])->name('unidades.update');
            Route::delete('/unidades/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'destroyUnidad'])->name('unidades.destroy');
            
            // Metas
            Route::get('/metas', [\App\Http\Controllers\Admin\AdminPanelController::class, 'metas'])->name('metas');
            Route::post('/metas', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeMeta'])->name('metas.store');
            Route::put('/metas/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateMeta'])->name('metas.update');
            Route::delete('/metas/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'destroyMeta'])->name('metas.destroy');
            
            // Actividades
            Route::get('/actividades', [\App\Http\Controllers\Admin\AdminPanelController::class, 'actividades'])->name('actividades');
            Route::post('/actividades', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeActividad'])->name('actividades.store');
            Route::put('/actividades/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateActividad'])->name('actividades.update');
            Route::delete('/actividades/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'destroyActividad'])->name('actividades.destroy');
            
            // Catálogos (Metas y Objetivos Predeterminados)
            Route::get('/catalogos', [\App\Http\Controllers\Admin\CatalogController::class, 'index'])->name('catalogos');
            Route::post('/catalogos/metas', [\App\Http\Controllers\Admin\CatalogController::class, 'storeMeta'])->name('catalogos.metas.store');
            Route::delete('/catalogos/metas/{id}', [\App\Http\Controllers\Admin\CatalogController::class, 'destroyMeta'])->name('catalogos.metas.destroy');
            Route::post('/catalogos/objetivos', [\App\Http\Controllers\Admin\CatalogController::class, 'storeObjetivo'])->name('catalogos.objetivos.store');
            Route::delete('/catalogos/objetivos/{id}', [\App\Http\Controllers\Admin\CatalogController::class, 'destroyObjetivo'])->name('catalogos.objetivos.destroy');
        });
    });

});

require __DIR__.'/auth.php';
