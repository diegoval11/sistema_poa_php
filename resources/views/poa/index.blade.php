@extends('layouts.poa')

@section('content')
<div class="space-y-8 pb-10 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-end gap-4 pb-2">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Listado de Proyectos</h1>
            <p class="text-gray-500 mt-1 font-medium">Historial completo de tus Planes Operativos Anuales.</p>
        </div>
        <a href="{{ route('poa.wizard.step1') }}" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 border-0 shadow-lg shadow-congress-blue-200 text-white px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
           Crear Nuevo Proyecto
        </a>
    </div>

    {{-- Contenedor de Tabla --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-white flex justify-between items-center">
            <h2 class="text-lg font-bold text-congress-blue-800">Todos los Registros</h2>
            <span class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1 rounded-full">{{ $proyectos->count() }} Proyectos</span>
        </div>

        <div class="p-0">
            @if($proyectos->count() > 0)
                <x-poa.project-table :proyectos="$proyectos" />
            @else
                <div class="text-center py-20">
                    <div class="bg-gray-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">No se encontraron proyectos</h3>
                    <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">Su historial está vacío. Comience una nueva planificación estratégica.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- IMPLEMENTACIÓN DEL COMPONENTE MODAL GLOBAL --}}
<x-modals.project-action-modal />

@endsection
