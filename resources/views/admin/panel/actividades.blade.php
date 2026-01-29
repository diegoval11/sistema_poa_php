@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">

    {{-- Header with Breadcrumb --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 pb-2">
        <div>
            <div class="text-sm breadcrumbs mb-2">
                <ul>
                    <li><a href="{{ route('admin.panel.index') }}" class="text-congress-blue-600 hover:text-congress-blue-800">Panel Avanzado</a></li>
                    <li class="text-gray-600">Actividades</li>
                </ul>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Gestión de Actividades
            </h1>
            <p class="text-gray-500 mt-1 font-medium">Administra todas las actividades de las metas POA</p>
        </div>
        <a href="{{ route('admin.panel.index') }}" class="btn bg-white hover:bg-gray-50 text-congress-blue-700 border border-congress-blue-200 shadow-sm px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Panel
        </a>
    </div>

    {{-- Search and Actions --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            {{-- Search Form --}}
            <form method="GET" action="{{ route('admin.panel.actividades') }}" class="flex-1 max-w-md">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Buscar actividades..." 
                        class="input input-bordered w-full pl-10 rounded-xl border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200 bg-white"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>

            {{-- Create Button --}}
            <button onclick="document.getElementById('modal_crear').showModal()" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 border-0 shadow-lg text-white px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Actividad
            </button>
        </div>
    </div>

    {{-- Actividades Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="bg-congress-blue-50 text-congress-blue-900">
                    <tr>
                        <th class="font-bold">ID</th>
                        <th class="font-bold">Proyecto</th>
                        <th class="font-bold">Meta</th>
                        <th class="font-bold">Descripción</th>
                        <th class="font-bold">Unidad Medida</th>
                        <th class="font-bold">Cantidad</th>
                        <th class="font-bold">Costo</th>
                        <th class="font-bold text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actividades as $actividad)
                        <tr>
                            <td class="font-mono text-sm">{{ $actividad->id }}</td>
                            <td class="font-semibold text-sm">{{ $actividad->meta->proyecto->nombre ?? 'Sin proyecto' }}</td>
                            <td class="max-w-xs text-sm">
                                <p class="line-clamp-1">{{ $actividad->meta->descripcion ?? 'Sin meta' }}</p>
                            </td>
                            <td class="max-w-md">
                                <p class="line-clamp-2">{{ $actividad->descripcion }}</p>
                            </td>
                            <td>{{ $actividad->unidad_medida }}</td>
                            <td class="text-right">{{ number_format($actividad->cantidad_programada_total, 0) }}</td>
                            <td class="text-right font-semibold">${{ number_format($actividad->costo_estimado, 2) }}</td>
                            <td>
                                <div class="flex gap-2 justify-center">
                                    {{-- Editar --}}
                                    <button onclick='editActividad(@json($actividad))' class="btn btn-sm bg-blue-50 hover:bg-blue-100 text-blue-700 border-2 border-blue-300 hover:border-blue-400 gap-1 rounded-lg shadow-sm" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span class="hidden xl:inline">Editar</span>
                                    </button>
                                    {{-- Eliminar --}}
                                    <button onclick="confirmDeleteActividad({{ $actividad->id }}, '{{ addslashes($actividad->descripcion) }}')" class="btn btn-sm bg-red-50 hover:bg-red-100 text-red-700 border-2 border-red-300 hover:border-red-400 gap-1 rounded-lg shadow-sm" title="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span class="hidden xl:inline">Eliminar</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-500">
                                @if($search)
                                    No se encontraron resultados para "{{ $search }}"
                                @else
                                    No hay actividades registradas
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($actividades->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $actividades->appends(['search' => $search])->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal Crear Actividad --}}
<x-modal-form id="modal_crear" title="Crear Nueva Actividad" maxWidth="3xl">
    <form method="POST" action="{{ route('admin.panel.actividades.store') }}">
        @csrf
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Meta del Proyecto</label>
                    <select name="poa_meta_id" class="select select-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                        <option value="">Seleccione una meta</option>
                        @foreach(\App\Models\PoaMeta::with('proyecto')->orderBy('id', 'desc')->get() as $meta)
                            <option value="{{ $meta->id }}">{{ $meta->proyecto->nombre ?? 'Sin proyecto' }} - {{ Str::limit($meta->descripcion, 50) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Descripción de la Actividad</label>
                    <textarea name="descripcion" rows="3" class="textarea textarea-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required maxlength="500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Unidad de Medida</label>
                    <input type="text" name="unidad_medida" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required maxlength="100" placeholder="ej: Unidades, Personas, Documentos">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Cantidad Programada Total</label>
                    <input type="number" name="cantidad_programada_total" step="1" min="0" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Medio de Verificación</label>
                    <textarea name="medio_verificacion" rows="2" class="textarea textarea-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Recursos (Opcional)</label>
                    <textarea name="recursos" rows="2" class="textarea textarea-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Costo Estimado ($)</label>
                    <input type="number" name="costo_estimado" step="0.01" min="0" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        {{-- Hidden input para enviar 0 cuando checkbox está desmarcado --}}
                        <input type="hidden" name="es_cuantificable" value="0">
                        <input type="checkbox" name="es_cuantificable" class="checkbox checkbox-lg" value="1" checked>
                        <div>
                            <span class="label-text font-bold text-gray-700">Es Cuantificable</span>
                            <p class="text-sm text-gray-500">Marcar si esta actividad se puede medir numéricamente</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal_crear').close()" class="btn bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-gray-400 rounded-lg px-6 font-semibold shadow-sm">
                Cancelar
            </button>
            <button type="submit" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0 rounded-lg px-6 font-semibold shadow-lg">
                Crear Actividad
            </button>
        </div>
    </form>
</x-modal-form>

{{-- Modal Editar Actividad --}}
<x-modal-form id="modal_editar" title="Editar Actividad" maxWidth="3xl">
    <form id="form_editar" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Meta del Proyecto</label>
                    <select name="poa_meta_id" id="edit_meta_id" class="select select-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                        @foreach(\App\Models\PoaMeta::with('proyecto')->orderBy('id', 'desc')->get() as $meta)
                            <option value="{{ $meta->id }}">{{ $meta->proyecto->nombre ?? 'Sin proyecto' }} - {{ Str::limit($meta->descripcion, 50) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Descripción de la Actividad</label>
                    <textarea name="descripcion" id="edit_descripcion" rows="3" class="textarea textarea-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required maxlength="500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Unidad de Medida</label>
                    <input type="text" name="unidad_medida" id="edit_unidad_medida" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required maxlength="100">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Cantidad Programada Total</label>
                    <input type="number" name="cantidad_programada_total" id="edit_cantidad" step="1" min="0" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Medio de Verificación</label>
                    <textarea name="medio_verificacion" id="edit_medio_verificacion" rows="2" class="textarea textarea-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Recursos (Opcional)</label>
                    <textarea name="recursos" id="edit_recursos" rows="2" class="textarea textarea-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Costo Estimado ($)</label>
                    <input type="number" name="costo_estimado" id="edit_costo" step="0.01" min="0" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        {{-- Hidden input para enviar 0 cuando checkbox está desmarcado --}}
                        <input type="hidden" name="es_cuantificable" value="0">
                        <input type="checkbox" name="es_cuantificable" id="edit_es_cuantificable" class="checkbox checkbox-lg" value="1">
                        <div>
                            <span class="label-text font-bold text-gray-700">Es Cuantificable</span>
                            <p class="text-sm text-gray-500">Marcar si esta actividad se puede medir numéricamente</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal_editar').close()" class="btn bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-gray-400 rounded-lg px-6 font-semibold shadow-sm">
                Cancelar
            </button>
            <button type="submit" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0 rounded-lg px-6 font-semibold shadow-lg">
                Actualizar Actividad
            </button>
        </div>
    </form>
</x-modal-form>

{{-- Modal Eliminar Actividad --}}
<dialog id="modal_eliminar" class="modal">
    <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">¿Eliminar Actividad?</h3>
            <div class="mt-2">
                <p id="delete_message" class="text-sm text-gray-500">Esta acción eliminará permanentemente la actividad. <strong>No se puede deshacer.</strong></p>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
            <form id="form_eliminar" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm transition-colors">
                    Eliminar Actividad
                </button>
            </form>
            <form method="dialog" class="w-full sm:w-auto mt-3 sm:mt-0">
                <button class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-congress-blue-500 sm:text-sm">
                    Cancelar
                </button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-gray-900/20"><button>close</button></form>
</dialog>

@endsection

@push('scripts')
<script>
// Manejo de la relación entre es_cuantificable y cantidad_programada_total

// Para el modal de CREAR
document.addEventListener('DOMContentLoaded', function() {
    const createModal = document.getElementById('modal_crear');
    const createCheckbox = createModal.querySelector('input[name="es_cuantificable"]');
    const createCantidadInput = createModal.querySelector('input[name="cantidad_programada_total"]');
    
    function handleCreateCuantificableChange() {
        if (!createCheckbox.checked) {
            createCantidadInput.value = 0;
            createCantidadInput.readOnly = true;
            createCantidadInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        } else {
            createCantidadInput.readOnly = false;
            createCantidadInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
            if (createCantidadInput.value == 0) {
                createCantidadInput.value = '';
            }
        }
    }
    
    createCheckbox.addEventListener('change', handleCreateCuantificableChange);
    
    // Para el modal de EDITAR
    const editModal = document.getElementById('modal_editar');
    const editCheckbox = editModal.querySelector('input[name="es_cuantificable"]');
    const editCantidadInput = editModal.querySelector('input[name="cantidad_programada_total"]');
    
    function handleEditCuantificableChange() {
        if (!editCheckbox.checked) {
            editCantidadInput.value = 0;
            editCantidadInput.readOnly = true;
            editCantidadInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        } else {
            editCantidadInput.readOnly = false;
            editCantidadInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
            if (editCantidadInput.value == 0) {
                editCantidadInput.value = '';
            }
        }
    }
    
    editCheckbox.addEventListener('change', handleEditCuantificableChange);
});

function editActividad(actividad) {
    document.getElementById('form_editar').action = `/admin/panel/actividades/${actividad.id}`;
    document.getElementById('edit_meta_id').value = actividad.poa_meta_id;
    document.getElementById('edit_descripcion').value = actividad.descripcion;
    document.getElementById('edit_unidad_medida').value = actividad.unidad_medida;
    document.getElementById('edit_cantidad').value = actividad.cantidad_programada_total;
    document.getElementById('edit_medio_verificacion').value = actividad.medio_verificacion;
    document.getElementById('edit_recursos').value = actividad.recursos || '';
    document.getElementById('edit_costo').value = actividad.costo_estimado;
    document.getElementById('edit_es_cuantificable').checked = actividad.es_cuantificable;
    
    // Aplicar el estado correcto del campo cantidad basado en es_cuantificable
    const editCantidadInput = document.getElementById('edit_cantidad');
    if (!actividad.es_cuantificable) {
        editCantidadInput.readOnly = true;
        editCantidadInput.classList.add('bg-gray-100', 'cursor-not-allowed');
    } else {
        editCantidadInput.readOnly = false;
        editCantidadInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
    }
    
    document.getElementById('modal_editar').showModal();
}

function confirmDeleteActividad(id, descripcion) {
    const shortDesc = descripcion.length > 50 ? descripcion.substring(0, 50) + '...' : descripcion;
    document.getElementById('delete_message').innerHTML = `¿Estás seguro de que deseas eliminar la actividad "<strong>${shortDesc}</strong>"? Esta acción <strong class="text-red-600">NO se puede deshacer</strong>.`;
    document.getElementById('form_eliminar').action = `/admin/panel/actividades/${id}`;
    document.getElementById('modal_eliminar').showModal();
}
</script>
@endpush
