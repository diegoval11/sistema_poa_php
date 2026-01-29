<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PoaProyecto;
use App\Models\PoaMeta;
use App\Models\PoaActividad;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminPanelController extends Controller
{
    /**
     * Vista principal del panel avanzado - Selector de tablas
     */
    public function index()
    {
        return view('admin.panel.index');
    }

    /**
     * Listar proyectos con búsqueda, paginación y filtro de tab
     */
    public function proyectos(Request $request)
    {
        $search = $request->get('search', '');
        $tab = $request->get('tab', 'activos'); // 'activos' o 'archivados'
        $page = $request->get('page', 1);
        
        // Cache key único por búsqueda, tab y página
        $cacheKey = 'admin_proyectos_' . md5($search . $tab . $page);
        
        $proyectos = Cache::remember($cacheKey, 300, function() use ($search, $tab) {
            $query = PoaProyecto::with(['unidad' => function($q) {
                $q->select('id', 'name', 'unidad_id')->with(['unidad' => function($q2) {
                    $q2->select('id', 'nombre');
                }]);
            }]);
            
            // Filtrar según tab
            if ($tab === 'archivados') {
                $query->onlyTrashed();
            } else {
                // Tab activos (sin soft deleted)
                $query->whereNull('deleted_at');
            }
            
            // Aplicar búsqueda
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('objetivo_unidad', 'LIKE', "%{$search}%")
                      ->orWhere('estado', 'LIKE', "%{$search}%")
                      ->orWhere('anio', 'LIKE', "%{$search}%");
                });
            }
            
            return $query->orderBy('updated_at', 'desc')->paginate(15);
        });

        return view('admin.panel.proyectos', [
            'proyectos' => $proyectos,
            'search' => $search,
        ]);
    }

    /**
     * Crear nuevo proyecto
     */
    public function storeProyecto(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:2000|max:2100',
            'objetivo_unidad' => 'nullable|string',
            'estado' => 'required|in:BORRADOR,ENVIADO,APROBADO,RECHAZADO',
        ]);

        DB::transaction(function () use ($request) {
            PoaProyecto::create($request->only([
                'user_id', 'nombre', 'anio', 'objetivo_unidad', 'estado'
            ]));
        });

        // Invalidar caché de proyectos
        Cache::flush(); // Alternativamente: Cache::forget con patrón

        return redirect()->route('admin.panel.proyectos')
            ->with('success', 'Proyecto creado exitosamente');
    }

    /**
     * Actualizar proyecto existente
     */
    public function updateProyecto(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:2000|max:2100',
            'objetivo_unidad' => 'nullable|string',
            'estado' => 'required|in:BORRADOR,ENVIADO,APROBADO,RECHAZADO',
        ]);

        $proyecto = PoaProyecto::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($request, $proyecto) {
            $proyecto->update($request->only([
                'user_id', 'nombre', 'anio', 'objetivo_unidad', 'estado'
            ]));
        });

        // Invalidar caché de proyectos
        Cache::flush();

        return redirect()->route('admin.panel.proyectos')
            ->with('success', 'Proyecto actualizado exitosamente');
    }

    /**
     * Eliminar proyecto (soft delete)
     */
    public function destroyProyecto($id)
    {
        $proyecto = PoaProyecto::withTrashed()->findOrFail($id);

        if ($proyecto->trashed()) {
            // Si ya está eliminado, eliminación permanente
            $proyecto->forceDelete();
            $message = 'Proyecto eliminado permanentemente';
            $tab = 'archivados';
        } else {
            // Soft delete
            $proyecto->delete();
            $message = 'Proyecto movido a papelera';
            $tab = 'activos';
        }

        // Invalidar caché de proyectos
        Cache::flush();

        return redirect()->route('admin.panel.proyectos', ['tab' => $tab])
            ->with('success', $message);
    }

    /**
     * Restaurar proyecto eliminado
     */
    public function restoreProyecto($id)
    {
        $proyecto = PoaProyecto::withTrashed()->findOrFail($id);
        $proyecto->restore();

        // Invalidar caché de proyectos
        Cache::flush();

        return redirect()->route('admin.panel.proyectos', ['tab' => 'activos'])
            ->with('success', 'Proyecto restaurado exitosamente');
    }

    /**
     * Vaciar papelera (eliminar todos los proyectos soft deleted)
     */
    public function emptyTrash()
    {
        $count = PoaProyecto::onlyTrashed()->count();
        PoaProyecto::onlyTrashed()->forceDelete();

        // Invalidar caché de proyectos
        Cache::flush();

        return redirect()->route('admin.panel.proyectos', ['tab' => 'archivados'])
            ->with('success', "Se eliminaron permanentemente {$count} proyecto(s) de la papelera");
    }

    // ==================== METAS ====================

    /**
     * Listar metas con búsqueda y paginación
     */
    public function metas(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        
        $cacheKey = 'admin_metas_' . md5($search . $page);
        
        $metas = Cache::remember($cacheKey, 300, function() use ($search) {
            $query = PoaMeta::with(['proyecto' => function($q) {
                $q->select('id', 'nombre', 'user_id')->with(['unidad' => function($q2) {
                    $q2->select('id', 'name', 'unidad_id')->with(['unidad' => function($q3) {
                        $q3->select('id', 'nombre');
                    }]);
                }]);
            }])->withCount('actividades');
            
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('descripcion', 'LIKE', "%{$search}%")
                      ->orWhereHas('proyecto', function($q2) use ($search) {
                          $q2->where('nombre', 'LIKE', "%{$search}%");
                      });
                });
            }
            
            return $query->orderBy('created_at', 'desc')->paginate(15);
        });

        return view('admin.panel.metas', [
            'metas' => $metas,
            'search' => $search,
        ]);
    }

    /**
     * Crear nueva meta
     */
    public function storeMeta(Request $request)
    {
        $request->validate([
            'poa_proyecto_id' => 'required|exists:poa_proyectos,id',
            'descripcion' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            PoaMeta::create($request->only(['poa_proyecto_id', 'descripcion']));
        });

        Cache::flush();

        return redirect()->route('admin.panel.metas')
            ->with('success', 'Meta creada exitosamente');
    }

    /**
     * Actualizar meta existente
     */
    public function updateMeta(Request $request, $id)
    {
        $request->validate([
            'poa_proyecto_id' => 'required|exists:poa_proyectos,id',
            'descripcion' => 'required|string',
        ]);

        $meta = PoaMeta::findOrFail($id);

        DB::transaction(function () use ($request, $meta) {
            $meta->update($request->only(['poa_proyecto_id', 'descripcion']));
        });

        Cache::flush();

        return redirect()->route('admin.panel.metas')
            ->with('success', 'Meta actualizada exitosamente');
    }

    /**
     * Eliminar meta
     */
    public function destroyMeta($id)
    {
        $meta = PoaMeta::findOrFail($id);
        
        DB::transaction(function () use ($meta) {
            $meta->delete();
        });

        Cache::flush();

        return redirect()->route('admin.panel.metas')
            ->with('success', 'Meta eliminada exitosamente');
    }

    // ==================== ACTIVIDADES ====================

    /**
     * Listar actividades con búsqueda y paginación
     */
    public function actividades(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        
        $cacheKey = 'admin_actividades_' . md5($search . $page);
        
        $actividades = Cache::remember($cacheKey, 300, function() use ($search) {
            $query = PoaActividad::with(['meta' => function($q) {
                $q->select('id', 'descripcion', 'poa_proyecto_id')->with(['proyecto' => function($q2) {
                    $q2->select('id', 'nombre');
                }]);
            }]);
            
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('descripcion', 'LIKE', "%{$search}%")
                      ->orWhere('unidad_medida', 'LIKE', "%{$search}%")
                      ->orWhereHas('meta', function($q2) use ($search) {
                          $q2->where('descripcion', 'LIKE', "%{$search}%");
                      });
                });
            }
            
            return $query->orderBy('created_at', 'desc')->paginate(15);
        });

        return view('admin.panel.actividades', [
            'actividades' => $actividades,
            'search' => $search,
        ]);
    }

    /**
     * Crear nueva actividad
     */
    public function storeActividad(Request $request)
    {
        $request->validate([
            'poa_meta_id' => 'required|exists:poa_metas,id',
            'descripcion' => 'required|string|max:500',
            'unidad_medida' => 'required|string|max:100',
            'es_cuantificable' => 'boolean',
            'cantidad_programada_total' => 'required|numeric|min:0',
            'medio_verificacion' => 'required|string',
            'recursos' => 'nullable|string',
            'costo_estimado' => 'required|numeric|min:0',
        ]);

        // Validación de integridad de datos: relación entre es_cuantificable y cantidad
        $esCuantificable = $request->boolean('es_cuantificable', true);
        $cantidad = $request->cantidad_programada_total;

        if (!$esCuantificable && $cantidad != 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cantidad_programada_total' => 'La cantidad debe ser 0 para actividades no cuantificables']);
        }

        if ($esCuantificable && $cantidad <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cantidad_programada_total' => 'La cantidad debe ser mayor a 0 para actividades cuantificables']);
        }

        DB::transaction(function () use ($request, $esCuantificable) {
            PoaActividad::create([
                'poa_meta_id' => $request->poa_meta_id,
                'descripcion' => $request->descripcion,
                'unidad_medida' => $request->unidad_medida,
                'es_cuantificable' => $esCuantificable,
                'cantidad_programada_total' => $request->cantidad_programada_total,
                'medio_verificacion' => $request->medio_verificacion,
                'recursos' => $request->recursos,
                'costo_estimado' => $request->costo_estimado,
            ]);
        });

        Cache::flush();

        return redirect()->route('admin.panel.actividades')
            ->with('success', 'Actividad creada exitosamente');
    }

    /**
     * Actualizar actividad existente
     */
    public function updateActividad(Request $request, $id)
    {
        $request->validate([
            'poa_meta_id' => 'required|exists:poa_metas,id',
            'descripcion' => 'required|string|max:500',
            'unidad_medida' => 'required|string|max:100',
            'es_cuantificable' => 'boolean',
            'cantidad_programada_total' => 'required|numeric|min:0',
            'medio_verificacion' => 'required|string',
            'recursos' => 'nullable|string',
            'costo_estimado' => 'required|numeric|min:0',
        ]);

        // Validación de integridad de datos: relación entre es_cuantificable y cantidad
        $esCuantificable = $request->boolean('es_cuantificable');
        $cantidad = $request->cantidad_programada_total;

        if (!$esCuantificable && $cantidad != 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cantidad_programada_total' => 'La cantidad debe ser 0 para actividades no cuantificables']);
        }

        if ($esCuantificable && $cantidad <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cantidad_programada_total' => 'La cantidad debe ser mayor a 0 para actividades cuantificables']);
        }

        $actividad = PoaActividad::findOrFail($id);

        DB::transaction(function () use ($request, $actividad, $esCuantificable) {
            $actividad->update([
                'poa_meta_id' => $request->poa_meta_id,
                'descripcion' => $request->descripcion,
                'unidad_medida' => $request->unidad_medida,
                'es_cuantificable' => $esCuantificable,
                'cantidad_programada_total' => $request->cantidad_programada_total,
                'medio_verificacion' => $request->medio_verificacion,
                'recursos' => $request->recursos,
                'costo_estimado' => $request->costo_estimado,
            ]);
        });

        Cache::flush();

        return redirect()->route('admin.panel.actividades')
            ->with('success', 'Actividad actualizada exitosamente');
    }

    /**
     * Eliminar actividad
     */
    public function destroyActividad($id)
    {
        $actividad = PoaActividad::findOrFail($id);
        
        DB::transaction(function () use ($actividad) {
            $actividad->delete();
        });

        Cache::flush();

        return redirect()->route('admin.panel.actividades')
            ->with('success', 'Actividad eliminada exitosamente');
    }

    // ==================== USUARIOS ====================

    /**
     * Listar usuarios con búsqueda y paginación
     */
    public function usuarios(Request $request)
    {
        $search = $request->get('search', '');
        
        $usuarios = User::with('unidad')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('role', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.panel.usuarios', [
            'usuarios' => $usuarios,
            'search' => $search
        ]);
    }

    /**
     * Crear nuevo usuario
     */
    public function storeUsuario(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,unidad',
            'unidad_id' => 'nullable|exists:unidades,id',
        ]);

        DB::transaction(function () use ($request) {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role,
                'unidad_id' => $request->unidad_id,
                'debe_cambiar_clave' => true,
            ]);
        });

        return redirect()->route('admin.panel.usuarios')
            ->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Actualizar usuario existente
     */
    public function updateUsuario(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,unidad',
            'unidad_id' => 'nullable|exists:unidades,id',
        ]);

        DB::transaction(function () use ($request, $usuario) {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'unidad_id' => $request->unidad_id,
            ];

            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
                $data['debe_cambiar_clave'] = true;
            }

            $usuario->update($data);
        });

        return redirect()->route('admin.panel.usuarios')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Eliminar usuario
     */
    public function destroyUsuario($id)
    {
        $usuario = User::findOrFail($id);
        
        // Prevenir eliminar el propio usuario
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.panel.usuarios')
                ->with('error', 'No puedes eliminar tu propia cuenta');
        }

        // Verificar si el usuario tiene proyectos ACTIVOS (no soft deleted)
        $proyectosActivosCount = PoaProyecto::where('user_id', $id)->count();
        
        if ($proyectosActivosCount > 0) {
            return redirect()->route('admin.panel.usuarios')
                ->with('error', "No se puede eliminar el usuario porque tiene {$proyectosActivosCount} proyecto(s) activo(s). Por favor, elimine primero todos los proyectos del usuario antes de proceder.");
        }

        DB::transaction(function () use ($usuario, $id) {
            // Eliminar permanentemente los proyectos en papelera (soft deleted)
            PoaProyecto::onlyTrashed()->where('user_id', $id)->forceDelete();
            
            // Ahora eliminar el usuario
            $usuario->delete();
        });

        return redirect()->route('admin.panel.usuarios')
            ->with('success', 'Usuario eliminado exitosamente');
    }

    // ==================== UNIDADES ====================

    /**
     * Listar unidades con búsqueda y paginación
     */
    public function unidades(Request $request)
    {
        $search = $request->get('search', '');
        
        $unidades = Unidad::withCount('users')
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'LIKE', "%{$search}%");
            })
            ->orderBy('nombre', 'asc')
            ->paginate(15);

        return view('admin.panel.unidades', [
            'unidades' => $unidades,
            'search' => $search
        ]);
    }

    /**
     * Crear nueva unidad
     */
    public function storeUnidad(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:unidades,nombre',
        ]);

        DB::transaction(function () use ($request) {
            Unidad::create([
                'nombre' => $request->nombre,
                'activa' => $request->boolean('activa', true), // true por defecto
                'sin_reporte' => $request->boolean('sin_reporte', false),
            ]);
        });

        return redirect()->route('admin.panel.unidades')
            ->with('success', 'Unidad creada exitosamente');
    }

    /**
     * Actualizar unidad existente
     */
    public function updateUnidad(Request $request, $id)
    {
        $unidad = Unidad::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:unidades,nombre,' . $id,
        ]);

        DB::transaction(function () use ($request, $unidad) {
            $unidad->update([
                'nombre' => $request->nombre,
                'activa' => $request->boolean('activa'),
                'sin_reporte' => $request->boolean('sin_reporte'),
            ]);
        });

        return redirect()->route('admin.panel.unidades')
            ->with('success', 'Unidad actualizada exitosamente');
    }

    /**
     * Eliminar unidad
     */
    public function destroyUnidad($id)
    {
        $unidad = Unidad::findOrFail($id);
        
        // Verificar si tiene usuarios asociados
        if ($unidad->users()->count() > 0) {
            return redirect()->route('admin.panel.unidades')
                ->with('error', 'No se puede eliminar la unidad porque tiene usuarios asociados');
        }

        $unidad->delete();

        return redirect()->route('admin.panel.unidades')
            ->with('success', 'Unidad eliminada exitosamente');
    }
}
