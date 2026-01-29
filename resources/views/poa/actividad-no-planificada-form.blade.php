@extends('layouts.poa')

@section('content')
<div class="max-w-6xl mx-auto pb-20">
    
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-congress-blue-800">Actividades No Planificadas</h1>
            <p class="text-gray-500 font-medium">Gestión de actividades emergentes {{ $proyecto->anio }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 font-bold text-sm">
            &larr; Volver al Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden border-l-4 border-l-orange-400">
        {{-- Header Meta Especial --}}
        <div class="bg-orange-50 border-b border-orange-100 p-6 flex justify-between items-start">
            <div>
                <h4 class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-2">Contenedor de Actividades</h4>
                <p class="text-gray-900 font-semibold text-base">"{{ $meta->descripcion }}"</p>
            </div>
            <span class="bg-orange-100 text-orange-700 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide border border-orange-200">
                Modo: No Planificada
            </span>
        </div>

        {{-- FORMULARIO --}}
        <form action="{{ route('poa.no_planificadas.store', $proyecto->id) }}" method="POST" class="p-6 border-b border-gray-200">
            @csrf
            {{-- No enviamos poa_meta_id, el controller lo infiere --}}

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- 1. Descripción --}}
                <div class="md:col-span-8">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Descripción de la Actividad</label>
                    <textarea name="descripcion" rows="2" class="w-full border-gray-300 rounded-lg px-4 py-3 focus:ring-1 focus:ring-congress-blue-600 text-sm placeholder-gray-400 shadow-sm" placeholder="Describa la actividad no planificada..." required></textarea>
                </div>

                {{-- 2. Unidad de Medida --}}
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Unidad de Medida</label>
                    <select id="unidad_select" class="w-full border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-1 focus:ring-congress-blue-600 shadow-sm" onchange="checkUnidad(this)">
                        <option value="Informe">Informe</option>
                        <option value="Documento">Documento</option>
                        <option value="Persona">Persona</option>
                        <option value="Servicio">Servicio</option>
                        <option value="Otro">Otro...</option>
                    </select>
                    <input type="text" name="unidad_medida" id="unidad_input" class="w-full mt-2 border-gray-300 rounded-lg px-3 py-2 text-sm hidden" placeholder="Especifique...">
                </div>

                {{-- 3. Checkbox Cuantificable (solo informativo) --}}
                <div class="md:col-span-6">
                    <div class="h-[46px] flex items-center bg-gray-50 rounded-lg border border-gray-300 px-4 mt-[26px]">
                        <label class="cursor-pointer flex items-center gap-3 w-full">
                            <input type="checkbox" name="es_cuantificable" id="check_cuantificable" value="1" checked class="checkbox checkbox-sm checkbox-primary rounded border-gray-400">
                            <span class="text-sm font-bold text-gray-700">Cuantificable</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Las actividades no planificadas no requieren cantidad programada</p>
                </div>

                {{-- 4. Costo y Recursos --}}
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Costo Estimado ($)</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="costo_estimado" step="0.01" min="0" class="w-full border-gray-300 rounded-lg pl-7 pr-3 py-3 font-bold text-gray-900 focus:ring-1 focus:ring-congress-blue-600 shadow-sm" placeholder="0.00" required>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Detalle Recursos</label>
                    <input type="text" name="recursos" maxlength="500" class="w-full border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-1 focus:ring-congress-blue-600 shadow-sm" placeholder="Ej: Fondos propios...">
                </div>

                {{-- 5. Medio de Verificación --}}
                <div class="md:col-span-12">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Medio de Verificación</label>
                    <input type="text" name="medio_verificacion" class="w-full border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-1 focus:ring-congress-blue-600 shadow-sm" placeholder="Ej: Listado de asistencia..." required>
                </div>

                <div class="md:col-span-12 flex justify-end pt-2">
                    <button type="submit" class="btn bg-congress-blue-700 hover:bg-congress-blue-800 text-white border-0 px-6 rounded-lg font-bold text-sm shadow-sm h-10 min-h-0">
                        Registrar Solicitud
                    </button>
                </div>
            </div>
        </form>

        {{-- LISTADO DE ACTIVIDADES NO PLANIFICADAS --}}
        <div class="p-6 bg-gray-50/50">
            <h4 class="font-bold text-gray-800 text-xs uppercase tracking-wide mb-4">Mis Solicitudes ({{ $meta->actividades->where('es_no_planificada', true)->count() }})</h4>

            <div class="space-y-3">
                @forelse($meta->actividades->where('es_no_planificada', true) as $act)
                    <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-300 shadow-sm transition-all group">
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center gap-1">
                                <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-orange-100 text-orange-600 font-bold text-xs rounded border border-orange-200">
                                    NP
                                </span>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm mb-1">{{ $act->descripcion }}</p>
                                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded font-bold border border-green-200">ACTIVA</span>
                                    
                                    <span class="flex items-center gap-1 font-medium bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                        {{ Str::limit($act->medio_verificacion, 30) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            {{-- Botón Eliminar siempre visible --}}
                            <button type="button"
                                    onclick="openDeleteModal('{{ route('poa.no_planificadas.destroy', $act->id) }}')"
                                    class="text-red-500 bg-red-50 hover:bg-red-600 hover:text-white p-2 rounded transition-colors border border-red-100"
                                    title="Eliminar Actividad">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg bg-white">
                        <p class="text-gray-400 text-sm font-medium">No has registrado actividades no planificadas.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- MODAL GLOBAL --}}
<x-modals.confirm-delete />

@push('scripts')
<script>
    function checkUnidad(select) {
        const input = document.getElementById('unidad_input');
        if(select.value === 'Otro') {
            input.classList.remove('hidden'); input.value = ''; input.focus();
        } else {
            input.classList.add('hidden'); input.value = select.value;
        }
    }
    document.getElementById('unidad_input').value = document.getElementById('unidad_select').value;

    function openDeleteModal(url) {
        const modal = document.getElementById('delete_modal');
        const form = document.getElementById('delete_form');
        if(modal && form) {
            form.action = url;
            modal.showModal();
        }
    }
</script>
@endpush
@endsection
