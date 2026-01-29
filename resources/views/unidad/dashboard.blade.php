@extends('layouts.poa')

@section('content')
<div class="space-y-8 pb-10 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-end gap-4 pb-2">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Hola, {{ Auth::user()->unidad?->nombre ?? 'Unidad' }}
            </h1>
            <p class="text-gray-500 mt-1 font-medium">Panel de Control General</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('poa.no_planificadas.create') }}" class="btn bg-white hover:bg-gray-50 text-congress-blue-700 border border-congress-blue-200 shadow-sm px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Actividad No Planificada
            </a>
            <a href="{{ route('poa.wizard.step1') }}" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 border-0 shadow-lg shadow-congress-blue-200 text-white px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Crear Nuevo Proyecto
            </a>
        </div>
    </div>

    {{-- Stats Cards (Código original de stats mantenido igual) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- ... TUS CARDS DE ESTADISTICAS AQUÍ (Sin cambios) ... --}}
        @include('poa.partials.stats-cards', ['proyectos' => $proyectos, 'cumplimientoGeneral' => $cumplimientoGeneral]) {{-- Sugerencia: Mover las cards a un partial también si quieres limpiar más --}}
    </div>

    {{-- Sección de Proyectos --}}
    <div>
        <div class="flex justify-between items-center mb-4 px-1">
            <h2 class="text-lg font-bold text-gray-800">Actividad Reciente</h2>
            @if($proyectos->count() > 2)
                <a href="{{ route('poa.lista_proyectos') }}" class="text-sm font-semibold text-congress-blue-600 hover:text-congress-blue-800 flex items-center gap-1 transition-colors">
                    Ver historial completo
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            @endif
        </div>

        @if($proyectos->count() > 0)
            {{-- Tabla Componente --}}
            <x-poa.project-table :proyectos="$proyectosRecientes" />
        @else
            {{-- Empty State --}}
            <div class="text-center py-20 bg-white border border-gray-200 rounded-2xl border-dashed">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h3 class="text-gray-900 font-bold">Sin actividad</h3>
                <p class="text-gray-500 text-sm mt-1">No hay proyectos registrados este año.</p>
            </div>
        @endif
    </div>
</div>

{{-- IMPLEMENTACIÓN DEL COMPONENTE MODAL GLOBAL --}}
<x-modals.project-action-modal />

@endsection
