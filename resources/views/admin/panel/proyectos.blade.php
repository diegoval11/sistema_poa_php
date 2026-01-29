@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">

    {{-- Header with Breadcrumb --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 pb-2">
        <div>
            <div class="text-sm breadcrumbs mb-2">
                <ul>
                    <li><a href="{{ route('admin.panel.index') }}" class="text-congress-blue-600 hover:text-congress-blue-800">Panel Avanzado</a></li>
                    <li class="text-gray-600">Proyectos</li>
                </ul>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Gestión de Proyectos POA
            </h1>
            <p class="text-gray-500 mt-1 font-medium">Administra todos los proyectos del sistema</p>
        </div>
        <a href="{{ route('admin.panel.index') }}" class="btn bg-white hover:bg-gray-50 text-congress-blue-700 border border-congress-blue-200 shadow-sm px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Panel
        </a>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div role="tablist" class="tabs tabs-bordered border-b border-gray-200">
            <a role="tab" class="tab {{ request('tab', 'activos') === 'activos' ? 'tab-active text-congress-blue-700 border-congress-blue-700' : 'text-gray-600' }} py-4 px-6 font-semibold transition-colors" href="{{ route('admin.panel.proyectos', ['tab' => 'activos', 'search' => request('search')]) }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Proyectos Activos
                <span class="ml-2 badge badge-sm bg-congress-blue-100 text-congress-blue-700 border-0">{{ \App\Models\PoaProyecto::count() }}</span>
            </a>
            <a role="tab" class="tab {{ request('tab') === 'archivados' ? 'tab-active text-orange-700 border-orange-700' : 'text-gray-600' }} py-4 px-6 font-semibold transition-colors" href="{{ route('admin.panel.proyectos', ['tab' => 'archivados', 'search' => request('search')]) }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                Papelera
                <span class="ml-2 badge badge-sm bg-orange-100 text-orange-700 border-0">{{ \App\Models\PoaProyecto::onlyTrashed()->count() }}</span>
            </a>
        </div>

        {{-- Search and Actions Bar --}}
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
                {{-- Search Form --}}
                <form method="GET" action="{{ route('admin.panel.proyectos') }}" class="flex-1 max-w-md">
                    <input type="hidden" name="tab" value="{{ request('tab', 'activos') }}">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}" 
                            placeholder="Buscar proyectos..." 
                            class="input input-bordered w-full pl-10 rounded-xl border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200 bg-white"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </form>

                {{-- Action Buttons --}}
                <div class="flex gap-3">
                    @if(request('tab') === 'archivados')
                        {{-- Eliminar Todos de Papelera --}}
                        <button onclick="confirmDeleteAll()" class="btn bg-red-600 hover:bg-red-700 border-0 shadow-lg text-white px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Vaciar Papelera
                        </button>
                    @else
                        {{-- Crear Proyecto --}}
                        <button onclick="document.getElementById('modal_crear').showModal()" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 border-0 shadow-lg text-white px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nuevo Proyecto
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Proyectos Table --}}
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="bg-congress-blue-50 text-congress-blue-900">
                    <tr>
                        <th class="font-bold">ID</th>
                        <th class="font-bold">Nombre del Proyecto</th>
                        <th class="font-bold">Unidad</th>
                        <th class="font-bold">Año</th>
                        <th class="font-bold">Estado</th>
                        <th class="font-bold">Actualizado</th>
                        <th class="font-bold text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proyectos as $proyecto)
                        <tr class="{{ $proyecto->trashed() ? 'bg-orange-50/50' : '' }}">
                            <td class="font-mono text-sm">{{ $proyecto->id }}</td>
                            <td class="font-semibold">{{ $proyecto->nombre }}</td>
                            <td>{{ $proyecto->unidad->unidad->nombre ?? 'Sin unidad' }}</td>
                            <td>{{ $proyecto->anio }}</td>
                            <td>
                                @php
                                    $estadoConfig = match($proyecto->estado) {
                                        'BORRADOR' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300'],
                                        'ENVIADO' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-300'],
                                        'APROBADO' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-300'],
                                        'RECHAZADO' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-300'],
                                        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'border' => 'border-gray-300']
                                    };
                                @endphp
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg font-medium text-sm border {{ $estadoConfig['bg'] }} {{ $estadoConfig['text'] }} {{ $estadoConfig['border'] }}">
                                    {{ $proyecto->estado }}
                                </span>
                            </td>
                            <td class="text-sm text-gray-500">
                                {{ $proyecto->updated_at->diffForHumans() }}
                            </td>
                            <td>
                                <div class="flex gap-2 justify-center">
                                    @if($proyecto->trashed())
                                        {{-- Restaurar --}}
                                        <button onclick="confirmRestore({{ $proyecto->id }}, '{{ $proyecto->nombre }}')" class="btn btn-sm bg-green-50 hover:bg-green-100 text-green-700 border-2 border-green-300 hover:border-green-400 gap-1 rounded-lg shadow-sm" title="Restaurar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span class="hidden md:inline">Restaurar</span>
                                        </button>
                                        {{-- Eliminar permanentemente --}}
                                        <button onclick="confirmPermanentDelete({{ $proyecto->id }}, '{{ $proyecto->nombre }}')" class="btn btn-sm bg-red-50 hover:bg-red-100 text-red-700 border-2 border-red-300 hover:border-red-400 gap-1 rounded-lg shadow-sm" title="Eliminar permanentemente">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span class="hidden md:inline">Eliminar</span>
                                        </button>
                                    @else
                                        {{-- Editar --}}
                                        <button onclick="editProyecto({{ $proyecto->id }}, '{{ addslashes($proyecto->nombre) }}', {{ $proyecto->user_id }}, {{ $proyecto->anio }}, '{{ addslashes($proyecto->objetivo_unidad ?? '') }}', '{{ $proyecto->estado }}')" class="btn btn-sm bg-blue-50 hover:bg-blue-100 text-blue-700 border-2 border-blue-300 hover:border-blue-400 gap-1 rounded-lg shadow-sm" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span class="hidden md:inline">Editar</span>
                                        </button>
                                        {{-- Eliminar (Soft Delete) --}}
                                        <button onclick="confirmDelete({{ $proyecto->id }}, '{{ $proyecto->nombre }}')" class="btn btn-sm bg-orange-50 hover:bg-orange-100 text-orange-700 border-2 border-orange-300 hover:border-orange-400 gap-1 rounded-lg shadow-sm" title="Mover a papelera">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="hidden md:inline">Papelera</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                @if($search)
                                    No se encontraron resultados para "{{ $search }}"
                                @elseif(request('tab') === 'archivados')
                                    La papelera está vacía
                                @else
                                    No hay proyectos registrados
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($proyectos->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $proyectos->appends(['tab' => request('tab', 'activos'), 'search' => $search])->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal Crear Proyecto --}}
<x-modal-form id="modal_crear" title="Crear Nuevo Proyecto">
    <form method="POST" action="{{ route('admin.panel.proyectos.store') }}">
        @csrf
        <div class="p-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Unidad</label>
                    <select name="user_id" class="select select-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                        <option value="">Seleccione una unidad</option>
                        @foreach(\App\Models\User::where('role', 'unidad')->with('unidad')->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->unidad->nombre ?? $user->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Proyecto</label>
                    <input type="text" name="nombre" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Año</label>
                    <input type="number" name="anio" min="2000" max="2100" value="{{ date('Y') }}" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Objetivo de la Unidad</label>
                    <textarea name="objetivo_unidad" rows="3" class="textarea textarea-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Estado</label>
                    <select name="estado" class="select select-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                        <option value="BORRADOR">BORRADOR</option>
                        <option value="ENVIADO">ENVIADO</option>
                        <option value="APROBADO">APROBADO</option>
                        <option value="RECHAZADO">RECHAZADO</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal_crear').close()" class="btn bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-gray-400 rounded-lg px-6 font-semibold shadow-sm">
                Cancelar
            </button>
            <button type="submit" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0 rounded-lg px-6 font-semibold shadow-lg">
                Crear Proyecto
            </button>
        </div>
    </form>
</x-modal-form>

{{-- Modal Editar Proyecto --}}
<x-modal-form id="modal_editar" title="Editar Proyecto">
    <form id="form_editar" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Unidad</label>
                    <select name="user_id" id="edit_user_id" class="select select-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                        @foreach(\App\Models\User::where('role', 'unidad')->with('unidad')->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->unidad->nombre ?? $user->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Proyecto</label>
                    <input type="text" name="nombre" id="edit_nombre" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Año</label>
                    <input type="number" name="anio" id="edit_anio" min="2000" max="2100" class="input input-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Objetivo de la Unidad</label>
                    <textarea name="objetivo_unidad" id="edit_objetivo" rows="3" class="textarea textarea-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Estado</label>
                    <select name="estado" id="edit_estado" class="select select-bordered w-full rounded-lg border-2 border-gray-300 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200" required>
                        <option value="BORRADOR">BORRADOR</option>
                        <option value="ENVIADO">ENVIADO</option>
                        <option value="APROBADO">APROBADO</option>
                        <option value="RECHAZADO">RECHAZADO</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal_editar').close()" class="btn bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-gray-400 rounded-lg px-6 font-semibold shadow-sm">
                Cancelar
            </button>
            <button type="submit" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0 rounded-lg px-6 font-semibold shadow-lg">
                Actualizar Proyecto
            </button>
        </div>
    </form>
</x-modal-form>

{{-- Modal Eliminar (Soft Delete) --}}
<dialog id="modal_eliminar" class="modal">
    <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mb-4">
                <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">¿Mover a Papelera?</h3>
            <div class="mt-2">
                <p id="delete_message" class="text-sm text-gray-500">El proyecto será movido a papelera. Podrás restaurarlo después si es necesario.</p>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
            <form id="form_eliminar" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:text-sm transition-colors">
                    Mover a Papelera
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

{{-- Modal Restaurar --}}
<dialog id="modal_restaurar" class="modal">
    <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">¿Restaurar Proyecto?</h3>
            <div class="mt-2">
                <p id="restore_message" class="text-sm text-gray-500">El proyecto será restaurado y volverá a estar disponible.</p>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
            <form id="form_restaurar" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm transition-colors">
                    Restaurar Proyecto
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

{{-- Modal Eliminar Permanentemente --}}
<dialog id="modal_eliminar_permanente" class="modal">
    <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">¿Eliminar Permanentemente?</h3>
            <div class="mt-2">
                <p id="delete_permanent_message" class="text-sm text-gray-500">Esta acción eliminará permanentemente el proyecto. <strong>No se puede deshacer.</strong></p>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
            <form id="form_eliminar_permanente" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm transition-colors">
                    Eliminar Permanentemente
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

{{-- Modal Vaciar Papelera --}}
<dialog id="modal_vaciar_papelera" class="modal">
    <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">¿Vaciar Papelera?</h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500">Se eliminarán <strong class="text-red-600">{{ \App\Models\PoaProyecto::onlyTrashed()->count() }} proyectos</strong> permanentemente. Esta acción <strong>NO se puede deshacer</strong>.</p>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
            <form action="{{ route('admin.panel.proyectos.empty-trash') }}" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm transition-colors">
                    Vaciar Papelera
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
function editProyecto(id, nombre, userId, anio, objetivo, estado) {
    document.getElementById('form_editar').action = `/admin/panel/proyectos/${id}`;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_anio').value = anio;
    document.getElementById('edit_objetivo').value = objetivo;
    document.getElementById('edit_estado').value = estado;
    document.getElementById('modal_editar').showModal();
}

function confirmDelete(id, nombre) {
    document.getElementById('delete_message').innerHTML = `¿Estás seguro de que deseas mover a papelera el proyecto <strong>"${nombre}"</strong>?`;
    document.getElementById('form_eliminar').action = `/admin/panel/proyectos/${id}`;
    document.getElementById('modal_eliminar').showModal();
}

function confirmRestore(id, nombre) {
    document.getElementById('restore_message').innerHTML = `¿Estás seguro de que deseas restaurar el proyecto <strong>"${nombre}"</strong>?`;
    document.getElementById('form_restaurar').action = `/admin/panel/proyectos/${id}/restore`;
    document.getElementById('modal_restaurar').showModal();
}

function confirmPermanentDelete(id, nombre) {
    document.getElementById('delete_permanent_message').innerHTML = `¿Estás seguro de que deseas eliminar PERMANENTEMENTE el proyecto <strong>"${nombre}"</strong>? Esta acción <strong class="text-red-600">NO se puede deshacer</strong>.`;
    document.getElementById('form_eliminar_permanente').action = `/admin/panel/proyectos/${id}`;
    document.getElementById('modal_eliminar_permanente').showModal();
}

function confirmDeleteAll() {
    document.getElementById('modal_vaciar_papelera').showModal();
}
</script>
@endpush
