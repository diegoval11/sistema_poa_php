@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">
    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-congress-blue-700 line-clamp-2">
                @if($proyecto->nombre)
                {{ $proyecto->nombre }}
                @else
                <span class="italic text-gray-400">(Sin nombre)</span>
                @endif
            </h1>
            <p class="text-gray-500 mt-1">{{ $proyecto->unidad->unidad->nombre }} - Año {{ $proyecto->anio }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.proyectos.export.excel', $proyecto->id) }}" 
               class="btn btn-success btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Excel
            </a>
            <a href="{{ route('admin.proyectos.export.pdf', $proyecto->id) }}" 
               class="btn btn-error btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Exportar PDF
            </a>
            <a href="{{ route('admin.unidades.proyectos', $proyecto->user_id) }}" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success shadow-lg mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Información general del proyecto --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <h2 class="card-title text-congress-blue-700 text-2xl">Información General</h2>
                </div>
                <div>
                    @if($proyecto->estado == 'BORRADOR')
                    <span class="badge badge-ghost badge-lg">Borrador</span>
                    @elseif($proyecto->estado == 'ENVIADO')
                    <span class="badge badge-warning badge-lg">Enviado</span>
                    @elseif($proyecto->estado == 'APROBADO')
                    <span class="badge badge-success badge-lg">Aprobado</span>
                    @elseif($proyecto->estado == 'RECHAZADO')
                    <span class="badge badge-error badge-lg">Rechazado</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-semibold text-gray-600">Unidad Responsable</p>
                    <p class="text-lg">{{ $proyecto->unidad->unidad->nombre }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Año de Ejecución</p>
                    <p class="text-lg">{{ $proyecto->anio }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Fecha de Creación</p>
                    <p class="text-lg">{{ $proyecto->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Última Modificación</p>
                    <p class="text-lg">{{ $proyecto->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            @if($proyecto->objetivo_unidad)
            <div class="mt-4">
                <p class="text-sm font-semibold text-gray-600">Objetivo de la Unidad</p>
                <p class="text-base mt-2 p-3 bg-gray-50 rounded">{{ $proyecto->objetivo_unidad }}</p>
            </div>
            @endif

            @if($proyecto->estado == 'APROBADO' && $proyecto->fecha_aprobacion)
            <div class="mt-4 p-4 bg-success/10 rounded-lg border border-success/20">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-semibold text-success">Proyecto Aprobado</p>
                </div>
                <p class="text-sm"><strong>Fecha de aprobación:</strong> {{ $proyecto->fecha_aprobacion->format('d/m/Y H:i') }}</p>
            </div>
            @elseif($proyecto->estado == 'RECHAZADO')
            <div class="mt-4 p-4 bg-error/10 rounded-lg border border-error/20">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-error" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-semibold text-error">Proyecto Rechazado</p>
                </div>
                <p class="text-sm"><strong>Motivo de rechazo:</strong></p>
                <p class="text-sm mt-2 p-3 bg-base-100 rounded">{{ $proyecto->motivo_rechazo }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Estadísticas del proyecto --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat bg-base-100 shadow-xl rounded-lg">
            <div class="stat-figure text-congress-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div class="stat-title">Total Metas</div>
            <div class="stat-value text-congress-blue-700">{{ $proyecto->metas->count() }}</div>
        </div>

        <div class="stat bg-base-100 shadow-xl rounded-lg">
            <div class="stat-figure text-congress-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div class="stat-title">Total Actividades</div>
            <div class="stat-value text-congress-blue-700">{{ $total_actividades }}</div>
        </div>

        <div class="stat bg-base-100 shadow-xl rounded-lg">
            <div class="stat-figure text-congress-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title">Presupuesto Total</div>
            <div class="stat-value text-congress-blue-700 text-2xl">${{ number_format($presupuesto_total, 2) }}</div>
        </div>

        <div class="stat bg-base-100 shadow-xl rounded-lg">
            <div class="stat-figure text-congress-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
            </div>
            <div class="stat-title">Total Evidencias</div>
            <div class="stat-value text-congress-blue-700">{{ $total_evidencias }}</div>
        </div>
    </div>

    {{-- Metas y Actividades detalladas --}}
    @foreach($proyecto->metas as $meta)
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="badge badge-lg badge-primary bg-congress-blue-600 border-0">Meta {{ $loop->iteration }}</div>
                <h3 class="card-title text-congress-blue-600">{{ $meta->descripcion }}</h3>
            </div>

            {{-- Actividades de la meta --}}
            <div class="space-y-3">
                @foreach($meta->actividades as $actividad)
                <details class="collapse collapse-arrow bg-base-200">
                    <summary class="collapse-title font-medium cursor-pointer">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <span class="badge badge-sm badge-primary bg-congress-blue-600 border-0">{{ $loop->iteration }}</span>
                                <span class="line-clamp-1 flex-1">{{ $actividad->descripcion }}</span>
                            </div>
                            <div class="flex gap-2 text-xs">
                                <span class="badge badge-outline">{{ $actividad->unidad_medida }}</span>
                                <span class="badge badge-outline">${{ number_format($actividad->costo_estimado, 2) }}</span>
                                <span class="badge badge-info">{{ $actividad->evidencias->count() }} evidencias</span>
                            </div>
                        </div>
                    </summary>
                    <div class="collapse-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="space-y-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Unidad de Medida</p>
                                    <p class="text-base">{{ $actividad->unidad_medida }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Cantidad Programada</p>
                                    <p class="text-base">{{ $actividad->cantidad_programada_total }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Es Cuantificable</p>
                                    <p class="text-base">
                                        @if($actividad->es_cuantificable)
                                        <span class="badge badge-success">Sí</span>
                                        @else
                                        <span class="badge badge-ghost">No</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Costo Estimado</p>
                                    <p class="text-base">${{ number_format($actividad->costo_estimado, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Medio de Verificación</p>
                                    <p class="text-base">{{ $actividad->medio_verificacion ?? 'N/A' }}</p>
                                </div>
                                @if($actividad->recursos)
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Recursos Necesarios</p>
                                    <p class="text-base">{{ $actividad->recursos }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Programación mensual --}}
                        @if($actividad->programaciones->count() > 0)
                        <div class="mt-4">
                            <p class="text-sm font-semibold text-gray-600 mb-2">Programación Mensual</p>
                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                        <tr class="bg-base-300">
                                            <th class="w-32">Mes</th>
                                            <th class="text-center">Programado</th>
                                            <th class="text-center">Ejecutado</th>
                                            <th class="text-center">% Cumplimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($actividad->programaciones->sortBy('mes') as $prog)
                                        <tr class="hover">
                                            <td class="font-medium">
                                                {{ ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][$prog->mes] }}
                                            </td>
                                            <td class="text-center">{{ $prog->cantidad_programada }}</td>
                                            <td class="text-center">{{ $prog->cantidad_ejecutada ?? 0 }}</td>
                                            <td class="text-center">
                                                @if($prog->cantidad_programada > 0)
                                                    @php
                                                        $cumplimiento = round(($prog->cantidad_ejecutada / $prog->cantidad_programada) * 100, 2);
                                                    @endphp
                                                    <span class="text-sm">{{ $cumplimiento }}%</span>
                                                @else
                                                    <span class="badge badge-neutral badge-sm">No aplica</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        {{-- Evidencias --}}
                        @if($actividad->evidencias->count() > 0)
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-gray-600">Todas las Evidencias ({{ $actividad->evidencias->count() }})</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($actividad->evidencias as $evidencia)
                                <div class="card card-compact bg-base-100 border border-base-300 hover:shadow-md transition-shadow">
                                    <div class="card-body">
                                        <div class="flex items-start gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5 text-congress-blue-600 flex-shrink-0 mt-0.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-gray-700 line-clamp-2">{{ $evidencia->descripcion ?? 'Sin descripción' }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $evidencia->created_at->format('d/m/Y') }}</p>
                                                @if($evidencia->archivo)
                                                <a href="{{ Storage::url($evidencia->archivo) }}"
                                                    target="_blank" class="btn btn-xs bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0 mt-2 w-full">
                                                    Ver Evidencia
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </details>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    {{-- Acciones según estado --}}
    @if($proyecto->estado == 'ENVIADO')
    <div class="flex gap-3 justify-end">
        <button onclick="modalRechazar.showModal()" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-5 py-2.5 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Rechazar Proyecto
        </button>
        <button onclick="modalAprobar.showModal()" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-5 py-2.5 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Aprobar Proyecto
        </button>
    </div>

    {{-- Modal de confirmación de aprobación --}}
    <dialog id="modalAprobar" class="modal">
        <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">
            {{-- Icono y Header --}}
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-bold text-gray-900">Confirmar Aprobación</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">¿Está seguro de que desea aprobar<br>el proyecto <strong>"{{ $proyecto->nombre ?? '(Sin nombre)' }}"</strong>?</p>
                </div>
            </div>

            {{-- Footer de Acciones --}}
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.proyectos.aprobar', $proyecto->id) }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                        Confirmar Aprobación
                    </button>
                </form>

                <form method="dialog" class="w-full sm:w-auto mt-3 sm:mt-0">
                    <button class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-congress-blue-500 sm:text-sm">
                        Cancelar
                    </button>
                </form>
            </div>
        </div>

        {{-- Backdrop para cerrar al hacer clic fuera --}}
        <form method="dialog" class="modal-backdrop bg-gray-900/20">
            <button>close</button>
        </form>
    </dialog>

    {{-- Modal de rechazo --}}
    <dialog id="modalRechazar" class="modal">
        <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-md">
            {{-- Icono y Header --}}
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-bold text-gray-900">Rechazar Proyecto</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500 mb-4">Indique el motivo del rechazo del proyecto<br><strong>"{{ $proyecto->nombre ?? '(Sin nombre)' }}"</strong></p>
                    
                    <form id="form_rechazar" method="POST" action="{{ route('admin.proyectos.rechazar', $proyecto->id) }}">
                        @csrf
                        <textarea 
                            name="motivo_rechazo" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-congress-blue-600 focus:ring-1 focus:ring-congress-blue-600 transition-colors text-sm text-gray-800" 
                            placeholder="Motivo de rechazo (mínimo 10 caracteres)"
                            rows="4"
                            required
                            minlength="10"></textarea>
                    </form>
                </div>
            </div>

            {{-- Footer de Acciones --}}
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray- 100">
                <button type="submit" form="form_rechazar" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                    Confirmar Rechazo
                </button>

                <form method="dialog" class="w-full sm:w-auto mt-3 sm:mt-0">
                    <button class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-congress-blue-500 sm:text-sm">
                        Cancelar
                    </button>
                </form>
            </div>
        </div>

        {{-- Backdrop para cerrar al hacer clic fuera --}}
        <form method="dialog" class="modal-backdrop bg-gray-900/20">
            <button>close</button>
        </form>
    </dialog>
    @endif
</div>
@endsection
