@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">

    {{-- Header with Breadcrumb --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 pb-2">
        <div>
            <div class="text-sm breadcrumbs mb-2">
                <ul>
                    <li><a href="{{ route('admin.panel.index') }}" class="text-congress-blue-600 hover:text-congress-blue-800">Panel Avanzado</a></li>
                    <li class="text-gray-600">Unidades</li>
                </ul>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Gestión de Unidades
            </h1>
            <p class="text-gray-500 mt-1 font-medium">Administra unidades organizacionales del sistema</p>
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
            <form method="GET" action="{{ route('admin.panel.unidades') }}" class="flex-1 max-w-md">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Buscar unidades..." 
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
                Nueva Unidad
            </button>
        </div>
    </div>

    {{-- Unidades Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="bg-congress-blue-50 text-congress-blue-900">
                    <tr>
                        <th class="font-bold">ID</th>
                        <th class="font-bold">Nombre de Unidad</th>
                        <th class="font-bold">Usuarios</th>
                        <th class="font-bold">Estado</th>
                        <th class="font-bold">Sin Reporte</th>
                        <th class="font-bold text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unidades as $unidad)
                        <tr>
                            <td class="font-mono text-sm">{{ $unidad->id }}</td>
                            <td class="font-semibold">{{ $unidad->nombre }}</td>
                            <td>
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-sm bg-gray-100 text-gray-700">
                                    {{ $unidad->users_count }} usuario(s)
                                </span>
                            </td>
                            <td>
                                @if($unidad->activa)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg font-medium text-sm border bg-green-100 text-green-700 border-green-300">
                                        ACTIVA
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg font-medium text-sm border bg-gray-100 text-gray-700 border-gray-300">
                                        INACTIVA
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($unidad->sin_reporte)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg font-medium text-sm border bg-yellow-100 text-yellow-700 border-yellow-300">
                                        SÍ
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex gap-2 justify-center">
                                    {{-- Editar --}}
                                    <button onclick="editUnidad({{ $unidad->id }}, '{{ addslashes($unidad->nombre) }}', {{ $unidad->activa ? 'true' : 'false' }}, {{ $unidad->sin_reporte ? 'true' : 'false' }})" class="btn btn-sm bg-blue-50 hover:bg-blue-100 text-blue-700 border-2 border-blue-300 hover:border-blue-400 gap-1 rounded-lg shadow-sm" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span class="hidden md:inline">Editar</span>
                                    </button>
                                    {{-- Eliminar --}}
                                    <button onclick="confirmDeleteUnidad({{ $unidad->id }}, '{{ $unidad->nombre }}', {{ $unidad->users_count }})" class="btn btn-sm bg-red-50 hover:bg-red-100 text-red-700 border-2 border-red-300 hover:border-red-400 gap-1 rounded-lg shadow-sm" title="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span class="hidden md:inline">Eliminar</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-500">
                                @if($search)
                                    No se encontraron resultados para "{{ $search }}"
                                @else
                                    No hay unidades registradas
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($unidades->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $unidades->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal Crear Unidad --}}
<x-modal-form id="modal_crear" title="Crear Nueva Unidad">
    <form method="POST" action="{{ route('admin.panel.unidades.store') }}">
        @csrf
        <div class="p-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nombre de la Unidad</label>
                    <input type="text" name="nombre" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="activa" class="checkbox checkbox-lg" checked>
                        <div>
                            <span class="label-text font-bold text-gray-700">Unidad Activa</span>
                            <p class="text-sm text-gray-500">Las unidades inactivas no podrán acceder al sistema</p>
                        </div>
                    </label>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="sin_reporte" class="checkbox checkbox-lg">
                        <div>
                            <span class="label-text font-bold text-gray-700">Sin Reporte</span>
                            <p class="text-sm text-gray-500">Marcar si esta unidad no debe generar reportes POA</p>
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
                Crear Unidad
            </button>
        </div>
    </form>
</x-modal-form>

{{-- Modal Editar Unidad --}}
<x-modal-form id="modal_editar" title="Editar Unidad">
    <form id="form_editar" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nombre de la Unidad</label>
                    <input type="text" name="nombre" id="edit_nombre" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="activa" id="edit_activa" class="checkbox checkbox-lg">
                        <div>
                            <span class="label-text font-bold text-gray-700">Unidad Activa</span>
                            <p class="text-sm text-gray-500">Las unidades inactivas no podrán acceder al sistema</p>
                        </div>
                    </label>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="sin_reporte" id="edit_sin_reporte" class="checkbox checkbox-lg">
                        <div>
                            <span class="label-text font-bold text-gray-700">Sin Reporte</span>
                            <p class="text-sm text-gray-500">Marcar si esta unidad no debe generar reportes POA</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal_editar').close()\" class="btn bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-gray-400 rounded-lg px-6 font-semibold shadow-sm">
                Cancelar
            </button>
            <button type="submit" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0 rounded-lg px-6 font-semibold shadow-lg">
                Actualizar Unidad
            </button>
        </div>
    </form>
</x-modal-form>

{{-- Modal Eliminar Unidad --}}
<dialog id="modal_eliminar" class="modal">
    <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">¿Eliminar Unidad?</h3>
            <div class="mt-2">
                <p id="delete_message" class="text-sm text-gray-500">Esta acción eliminará permanentemente la unidad y no se puede deshacer.</p>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
            <form id="form_eliminar" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm transition-colors">
                    Eliminar Unidad
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
function editUnidad(id, nombre, activa, sinReporte) {
    document.getElementById('form_editar').action = `/admin/panel/unidades/${id}`;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_activa').checked = activa;
    document.getElementById('edit_sin_reporte').checked = sinReporte;
    document.getElementById('modal_editar').showModal();
}

function confirmDeleteUnidad(id, nombre, usersCount) {
    if (usersCount > 0) {
        document.getElementById('delete_message').innerHTML = `No se puede eliminar la unidad <strong>"${nombre}"</strong> porque tiene <strong>${usersCount}</strong> usuario(s) asociado(s). Primero debes reasignar o eliminar los usuarios.`;
        document.getElementById('form_eliminar').querySelector('button[type="submit"]').disabled = true;
        document.getElementById('form_eliminar').querySelector('button[type="submit"]').textContent = 'No se puede eliminar';
    } else {
        document.getElementById('delete_message').innerHTML = `¿Estás seguro de que deseas eliminar la unidad <strong>"${nombre}"</strong>? Esta acción <strong class="text-red-600">NO se puede deshacer</strong>.`;
        document.getElementById('form_eliminar').action = `/admin/panel/unidades/${id}`;
        document.getElementById('form_eliminar').querySelector('button[type="submit"]').disabled = false;
        document.getElementById('form_eliminar').querySelector('button[type="submit"]').textContent = 'Eliminar Unidad';
    }
    document.getElementById('modal_eliminar').showModal();
}
</script>
@endpush
