@extends('layouts.poa')

@section('content')
<div class="max-w-6xl mx-auto pb-20">
    <x-poa.steps :paso="3" />

    {{-- Tabs de Navegación --}}
    <div class="flex flex-wrap gap-2 mb-6 px-1">
        @foreach($proyecto->metas as $meta)
            <a href="?tab={{ $meta->id }}"
               class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide border transition-all
               {{ (request('tab') == $meta->id || (!request('tab') && $loop->first))
                   ? 'bg-congress-blue-700 text-white border-congress-blue-700 shadow-md'
                   : 'bg-white text-gray-500 border-gray-300 hover:border-gray-400 hover:text-gray-700' }}">
                Meta {{ $loop->iteration }}
                @if($meta->actividades->count() == 0)
                    <span class="ml-2 inline-block w-1.5 h-1.5 rounded-full bg-red-500"></span>
                @endif
            </a>
        @endforeach
    </div>

    @php
        $activeMeta = request('tab') ? $proyecto->metas->firstWhere('id', request('tab')) : $proyecto->metas->first();
    @endphp

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header Meta --}}
        <div class="bg-gray-50 border-b border-gray-200 p-6">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Meta Seleccionada</h4>
            <p class="text-gray-900 font-semibold text-base">"{{ $activeMeta->descripcion }}"</p>
        </div>

        {{-- FORMULARIO ACTUALIZADO --}}
        <form action="{{ route('poa.wizard.storeActividad', $proyecto->id) }}" method="POST" class="p-6 border-b border-gray-200">
            @csrf
            <input type="hidden" name="poa_meta_id" value="{{ $activeMeta->id }}">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- 1. Descripción --}}
                <div class="md:col-span-8">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Descripción de la Actividad</label>
                    <textarea name="descripcion" rows="2" class="w-full border-gray-300 rounded-lg px-4 py-3 focus:ring-1 focus:ring-congress-blue-600 text-sm placeholder-gray-400 shadow-sm" placeholder="Describa la actividad..." required></textarea>
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

                {{-- 3. Check y Cantidad Total --}}
                <div class="md:col-span-3">
                    <div class="h-[46px] flex items-center bg-gray-50 rounded-lg border border-gray-300 px-4 mt-[26px]">
                        <label class="cursor-pointer flex items-center gap-3 w-full">
                            <input type="checkbox" name="es_cuantificable" id="check_cuantificable" value="1" checked class="checkbox checkbox-sm checkbox-primary rounded border-gray-400" onchange="toggleCantidad(this)">
                            <span class="text-sm font-bold text-gray-700">Cuantificable</span>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Cantidad Total</label>
                    <input type="number" name="cantidad_programada_total" id="input_cantidad" class="w-full border-gray-300 rounded-lg px-3 py-3 font-bold text-center text-gray-900 focus:ring-1 focus:ring-congress-blue-600 shadow-sm" placeholder="0" min="1" step="1" required>
                </div>

                {{-- 4. NUEVOS CAMPOS: Costo y Recursos --}}
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Total Recursos ($)</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="costo_estimado" step="0.01" min="0" class="w-full border-gray-300 rounded-lg pl-7 pr-3 py-3 font-bold text-gray-900 focus:ring-1 focus:ring-congress-blue-600 shadow-sm" placeholder="0.00" required>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Detalle Recursos</label>
                    <input type="text" name="recursos" maxlength="500" class="w-full border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-1 focus:ring-congress-blue-600 shadow-sm" placeholder="Ej: Fondos propios, FODES...">
                </div>

                {{-- 5. Medio de Verificación (Full Width) --}}
                <div class="md:col-span-12">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Medio de Verificación</label>
                    <input type="text" name="medio_verificacion" class="w-full border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-1 focus:ring-congress-blue-600 shadow-sm" placeholder="Ej: Listado de asistencia, Fotografía, Acta de recepción..." required>
                </div>

                <div class="md:col-span-12 flex justify-end pt-2">
                    <button type="submit" class="btn bg-congress-blue-700 hover:bg-congress-blue-800 text-white border-0 px-6 rounded-lg font-bold text-sm shadow-sm h-10 min-h-0">
                        Guardar Actividad
                    </button>
                </div>
            </div>
        </form>

        {{-- LISTADO DE ACTIVIDADES --}}
        <div class="p-6 bg-gray-50/50">
            <h4 class="font-bold text-gray-800 text-xs uppercase tracking-wide mb-4">Actividades Registradas ({{ $activeMeta->actividades->count() }})</h4>

            <div class="space-y-3">
                @forelse($activeMeta->actividades as $act)
                    <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-300 shadow-sm transition-all group">
                        <div class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-600 font-bold text-xs rounded border border-gray-200">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <p class="font-bold text-gray-800 text-sm mb-1">{{ $act->descripcion }}</p>
                                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                                    <span class="flex items-center gap-1 font-medium bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                        {{ Str::limit($act->medio_verificacion, 30) }}
                                    </span>

                                    {{-- Mostrar Recurso si existe --}}
                                    @if($act->costo_estimado > 0)
                                        <span class="flex items-center gap-1 font-bold text-green-700 bg-green-50 px-2 py-0.5 rounded border border-green-100">
                                            ${{ number_format($act->costo_estimado, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($act->es_cuantificable)
                                <div class="text-right">
                                    <span class="block text-lg font-black text-gray-900 leading-none">{{ $act->cantidad_programada_total }}</span>
                                    <span class="text-[10px] uppercase font-bold text-gray-400">{{ $act->unidad_medida }}</span>
                                </div>
                            @else
                                <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded border border-gray-200">Cualitativa</span>
                            @endif

                            <div class="h-8 w-px bg-gray-200"></div>

                            @if($proyecto->estado === 'BORRADOR')
                                {{-- Botón Editar --}}
                                <button type="button"
                                        onclick='openEditActividadModal(@json($act))'
                                        class="text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 p-2 rounded transition-colors border border-blue-100"
                                        title="Editar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>

                                {{-- Botón Eliminar --}}
                                <button type="button"
                                        onclick="openDeleteModal('{{ route('poa.wizard.deleteActividad', $act->id) }}')"
                                        class="text-red-500 bg-red-50 hover:bg-red-600 hover:text-white p-2 rounded transition-colors border border-red-100"
                                        title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg bg-white">
                        <p class="text-gray-400 text-sm font-medium">No hay actividades en esta meta.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Navegación --}}
    <div class="mt-8 flex justify-between items-center">
        <a href="{{ route('poa.wizard.step2', $proyecto->id) }}" class="text-gray-600 hover:text-gray-900 font-bold text-sm transition-colors border border-gray-300 bg-white px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50">
            Anterior
        </a>

        {{-- Lógica de validación visual del botón (Solo UI, la seguridad real está en el Validator) --}}
        @php
            $allMetasHaveActivities = $proyecto->metas->every(fn($m) => $m->actividades->count() > 0);
        @endphp

        @if($allMetasHaveActivities)
            <a href="{{ route('poa.wizard.step4', $proyecto->id) }}" class="btn bg-congress-blue-700 hover:bg-congress-blue-800 text-white border-0 rounded-lg px-8 font-bold shadow-md h-10 min-h-0">
                Siguiente Paso
            </a>
        @else
            <button disabled class="btn btn-disabled bg-gray-200 text-gray-400 border-0 rounded-lg px-8 font-bold h-10 min-h-0 cursor-not-allowed">
                Siguiente Paso
            </button>
        @endif
    </div>
</div>

{{-- MODAL GLOBAL --}}
<x-modals.confirm-delete />

{{-- Modal Editar Actividad --}}
<dialog id="edit_actividad_modal" class="modal">
    <div class="modal-box bg-white rounded-xl p-0 shadow-2xl max-w-4xl">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Editar Actividad</h3>
        </div>
        <form id="edit_actividad_form" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    {{-- Descripción --}}
                    <div class="md:col-span-8">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Descripción</label>
                        <textarea name="descripcion" id="edit_descripcion" rows="2" class="w-full border-gray-300 rounded-lg px-4 py-3 focus:ring-1 focus:ring-congress-blue-600 text-sm" required></textarea>
                    </div>

                    {{-- Unidad de Medida --}}
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Unidad de Medida</label>
                        <select id="edit_unidad_select" class="w-full border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-1 focus:ring-congress-blue-600" onchange="checkEditUnidad(this)">
                            <option value="Informe">Informe</option>
                            <option value="Documento">Documento</option>
                            <option value="Persona">Persona</option>
                            <option value="Servicio">Servicio</option>
                            <option value="Otro">Otro...</option>
                        </select>
                        <input type="text" name="unidad_medida" id="edit_unidad_input" class="w-full mt-2 border-gray-300 rounded-lg px-3 py-2 text-sm hidden" placeholder="Especifique...">
                    </div>

                    {{-- Checkbox Cuantificable --}}
                    <div class="md:col-span-3">
                        <div class="h-[46px] flex items-center bg-gray-50 rounded-lg border border-gray-300 px-4 mt-[26px]">
                            <label class="cursor-pointer flex items-center gap-3 w-full">
                                <input type="checkbox" name="es_cuantificable" id="edit_check_cuantificable" value="1" checked class="checkbox checkbox-sm checkbox-primary rounded border-gray-400" onchange="toggleEditCantidad(this)">
                                <span class="text-sm font-bold text-gray-700">Cuantificable</span>
                            </label>
                        </div>
                    </div>

                    {{-- Cantidad Total --}}
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Cantidad Total</label>
                        <input type="number" name="cantidad_programada_total" id="edit_input_cantidad" class="w-full border-gray-300 rounded-lg px-3 py-3 font-bold text-center text-gray-900 focus:ring-1 focus:ring-congress-blue-600" placeholder="0" min="1" step="1" required>
                    </div>

                    {{-- Costo --}}
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Total Recursos ($)</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="costo_estimado" id="edit_costo_estimado" step="0.01" min="0" class="w-full border-gray-300 rounded-lg pl-7 pr-3 py-3 font-bold text-gray-900 focus:ring-1 focus:ring-congress-blue-600" placeholder="0.00" required>
                        </div>
                    </div>

                    {{-- Recursos --}}
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Detalle Recursos</label>
                        <input type="text" name="recursos" id="edit_recursos" maxlength="500" class="w-full border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-1 focus:ring-congress-blue-600" placeholder="Ej: Fondos propios, FODES...">
                    </div>

                    {{-- Medio Verificación --}}
                    <div class="md:col-span-12">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Medio de Verificación</label>
                        <input type="text" name="medio_verificacion" id="edit_medio_verificacion" class="w-full border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-1 focus:ring-congress-blue-600" placeholder="Ej: Listado de asistencia, Fotografía..." required>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-xl border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('edit_actividad_modal').close()" class="btn bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-gray-400 rounded-lg px-6 font-semibold shadow-sm">
                    Cancelar
                </button>
                <button type="submit" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0 rounded-lg px-6 font-semibold shadow-lg">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop bg-gray-900/20"><button>close</button></form>
</dialog>

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

    function toggleCantidad(checkbox) {
        const input = document.getElementById('input_cantidad');
        if(checkbox.checked) {
            input.disabled = false; input.required = true; input.classList.remove('bg-gray-100');
        } else {
            input.disabled = true; input.required = false; input.value = 0; input.classList.add('bg-gray-100');
        }
    }

    function openDeleteModal(url) {
        const modal = document.getElementById('delete_modal');
        const form = document.getElementById('delete_form');
        if(modal && form) {
            form.action = url;
            modal.showModal();
        }
    }

    // Funciones para el modal de edición
    function openEditActividadModal(actividad) {
        const modal = document.getElementById('edit_actividad_modal');
        const form = document.getElementById('edit_actividad_form');
        
        if(modal && form) {
            // Set form action
            form.action = `/poa/wizard/actualizar-actividad/${actividad.id}`;
            
            // Populate fields
            document.getElementById('edit_descripcion').value = actividad.descripcion;
            document.getElementById('edit_medio_verificacion').value = actividad.medio_verificacion;
            document.getElementById('edit_costo_estimado').value = actividad.costo_estimado || 0;
            document.getElementById('edit_recursos').value = actividad.recursos || '';
            
            // Handle unidad_medida
            const unidadSelect = document.getElementById('edit_unidad_select');
            const unidadInput = document.getElementById('edit_unidad_input');
            const predefinedOptions = ['Informe', 'Documento', 'Persona', 'Servicio'];
            
            if (predefinedOptions.includes(actividad.unidad_medida)) {
                unidadSelect.value = actividad.unidad_medida;
                unidadInput.classList.add('hidden');
                unidadInput.value = actividad.unidad_medida;
            } else {
                unidadSelect.value = 'Otro';
                unidadInput.classList.remove('hidden');
                unidadInput.value = actividad.unidad_medida;
            }
            
            // Handle cuantificable checkbox
            const checkCuantificable = document.getElementById('edit_check_cuantificable');
            const inputCantidad = document.getElementById('edit_input_cantidad');
            
            checkCuantificable.checked = actividad.es_cuantificable;
            if (actividad.es_cuantificable) {
                inputCantidad.disabled = false;
                inputCantidad.required = true;
                inputCantidad.value = actividad.cantidad_programada_total;
                inputCantidad.classList.remove('bg-gray-100');
            } else {
                inputCantidad.disabled = true;
                inputCantidad.required = false;
                inputCantidad.value = 0;
                inputCantidad.classList.add('bg-gray-100');
            }
            
            modal.showModal();
        }
    }

    function checkEditUnidad(select) {
        const input = document.getElementById('edit_unidad_input');
        if(select.value === 'Otro') {
            input.classList.remove('hidden');
            input.value = '';
            input.focus();
        } else {
            input.classList.add('hidden');
            input.value = select.value;
        }
    }

    function toggleEditCantidad(checkbox) {
        const input = document.getElementById('edit_input_cantidad');
        if(checkbox.checked) {
            input.disabled = false;
            input.required = true;
            input.classList.remove('bg-gray-100');
        } else {
            input.disabled = true;
            input.required = false;
            input.value = 0;
            input.classList.add('bg-gray-100');
        }
    }
</script>
@endpush
@endsection
