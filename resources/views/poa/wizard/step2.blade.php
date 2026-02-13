@extends('layouts.poa')

@section('content')
<div class="max-w-5xl mx-auto pb-20">
    <x-poa.steps :paso="2" />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- COLUMNA IZQUIERDA: Formulario --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h3 class="font-bold text-gray-900 text-base">Nueva Meta</h3>
                    <p class="text-xs text-gray-500 mt-1">Defina el objetivo físico a alcanzar.</p>
                </div>

                <form action="{{ route('poa.wizard.storeMeta', $proyecto->id) }}" method="POST">
                    @csrf

                    <div class="space-y-5">
                        <div class="form-control">
                            <label class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-2 block">Selección Rápida</label>
                            <div class="relative">
                                <select name="meta_predeterminada" id="meta-select" class="w-full appearance-none bg-white border border-gray-300 text-gray-700 py-3 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:border-congress-blue-600 focus:ring-1 focus:ring-congress-blue-600 transition-colors text-sm font-medium" required>
                                    <option value="" disabled selected>Seleccione una opción...</option>
                                    @foreach($metasPredeterminadas as $meta)
                                        <option value="{{ $meta }}">{{ $meta }}</option>
                                    @endforeach
                                    <option value="OTRA" class="font-bold text-congress-blue-700">Crear personalizada</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <div id="campo-meta-custom" class="hidden">
                            <label class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-2 block">Descripción Manual</label>
                            <textarea name="descripcion" id="meta-input" rows="4" class="w-full bg-white border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-congress-blue-600 focus:ring-1 focus:ring-congress-blue-600 transition-colors text-sm text-gray-800 placeholder-gray-400" placeholder="Redacte la meta detalladamente..."></textarea>
                        </div>

                        <button type="submit" class="w-full btn bg-congress-blue-700 hover:bg-congress-blue-800 text-white border-0 rounded-lg shadow-sm font-bold text-sm h-12 min-h-0">
                            Agregar Meta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Listado --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-0 flex flex-col h-full overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wide">Listado de Metas</h3>
                    <span class="bg-white border border-gray-200 text-gray-600 text-xs font-bold px-2.5 py-0.5 rounded">{{ $proyecto->metas->count() }}</span>
                </div>

                <div class="p-6 space-y-4 flex-grow bg-gray-50/30">
                    @forelse($proyecto->metas as $meta)
                        <div class="flex items-start gap-4 p-4 bg-white border border-gray-200 rounded-lg shadow-[0_2px_4px_rgba(0,0,0,0.02)] hover:border-congress-blue-300 transition-colors group">
                            {{-- Número --}}
                            <span class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded bg-gray-100 text-gray-600 font-bold text-xs border border-gray-200 group-hover:bg-congress-blue-50 group-hover:text-congress-blue-700 group-hover:border-congress-blue-200 transition-colors">
                                {{ $loop->iteration }}
                            </span>

                            {{-- Contenido --}}
                            <p class="flex-grow text-gray-700 text-sm font-medium leading-relaxed pt-0.5">{{ $meta->descripcion }}</p>

                            <div class="flex-shrink-0 flex items-center gap-2">
                                {{-- Botón Editar (Solo si estado es BORRADOR) --}}
                                @if($proyecto->estado === 'BORRADOR')
                                    <button type="button" onclick="openEditMetaModal({{ $meta->id }}, '{{ addslashes($meta->descripcion) }}')" class="text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 p-2 rounded transition-colors border border-blue-100" title="Editar Meta">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                @endif
                                
                                {{-- Botón Eliminar (Solo si estado es BORRADOR) --}}
                                @if($proyecto->estado === 'BORRADOR')
                                    <button type="button" onclick="openDeleteModal('{{ route('poa.wizard.deleteMeta', $meta->id) }}')" class="text-red-500 bg-red-50 hover:bg-red-100 hover:text-red-700 p-2 rounded transition-colors border border-red-100" title="Eliminar Meta">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center text-center py-16 border-2 border-dashed border-gray-200 rounded-lg">
                            <span class="text-gray-400 mb-2 font-medium">Sin registros</span>
                            <p class="text-gray-500 text-xs">Utilice el formulario para agregar su primera meta.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Navegación --}}
                <div class="px-6 py-5 border-t border-gray-200 bg-white flex justify-between items-center">
                    <a href="{{ route('poa.wizard.step1', $proyecto->id) }}" class="text-gray-500 hover:text-gray-900 font-medium text-sm transition-colors">
                        Anterior
                    </a>

                    @if($proyecto->metas->count() > 0)
                        <a href="{{ route('poa.wizard.step3', $proyecto->id) }}" class="btn bg-congress-blue-700 hover:bg-congress-blue-800 text-white border-0 rounded-lg px-6 h-10 min-h-0 font-bold text-sm shadow-sm">
                            Siguiente Paso
                        </a>
                    @else
                        <button disabled class="btn btn-disabled bg-gray-100 text-gray-400 border-gray-200 rounded-lg px-6 h-10 min-h-0 font-bold text-sm">
                            Siguiente Paso
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmación de eliminación --}}
<x-modals.confirm-delete />

{{-- Modal de edición de meta --}}
<dialog id="edit_meta_modal" class="modal">
    <div class="modal-box bg-white rounded-xl p-0 shadow-2xl max-w-lg">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Editar Meta</h3>
        </div>
        <form id="edit_meta_form" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6">
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Descripción</label>
                <textarea name="descripcion" id="edit_meta_descripcion" rows="4" class="w-full bg-white border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-congress-blue-600 focus:ring-1 focus:ring-congress-blue-600 transition-colors text-sm text-gray-800" required></textarea>
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-xl border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('edit_meta_modal').close()" class="btn bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-gray-400 rounded-lg px-6 font-semibold shadow-sm">
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
    // Tu lógica existente para el select
    const metaSelect = document.getElementById('meta-select');
    const campoCustom = document.getElementById('campo-meta-custom');
    const metaInput = document.getElementById('meta-input');

    metaSelect.addEventListener('change', function() {
        if(this.value === 'OTRA') {
            campoCustom.classList.remove('hidden');
            metaInput.required = true; metaInput.focus();
        } else {
            campoCustom.classList.add('hidden');
            metaInput.required = false;
        }
    });

    // Lógica para abrir el modal de eliminación
    function openDeleteModal(url) {
        const modal = document.getElementById('delete_modal');
        const form = document.getElementById('delete_form');
        if(modal && form) {
            form.action = url;
            modal.showModal();
        }
    }

    // Lógica para abrir el modal de edición
    function openEditMetaModal(id, descripcion) {
        const modal = document.getElementById('edit_meta_modal');
        const form = document.getElementById('edit_meta_form');
        const textarea = document.getElementById('edit_meta_descripcion');
        
        if(modal && form && textarea) {
            form.action = `/poa/wizard/actualizar-meta/${id}`;
            textarea.value = descripcion;
            modal.showModal();
        }
    }
</script>
@endpush
@endsection
