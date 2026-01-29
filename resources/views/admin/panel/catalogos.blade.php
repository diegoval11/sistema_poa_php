@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-end gap-4 pb-2">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Metas y Objetivos Predeterminados
            </h1>
            <p class="text-gray-500 mt-1 font-medium">Gestiona catálogos de opciones predeterminadas para el Wizard de Proyectos</p>
        </div>
        <a href="{{ route('admin.panel.index') }}" class="btn bg-white hover:bg-gray-50 text-congress-blue-700 border border-congress-blue-200 shadow-sm px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Panel
        </a>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
            <p class="text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <p class="text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Grid de 2 columnas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Metas Predeterminadas --}}
        <div class="bg-white rounded-2xl shadow-lg border border-rose-100 overflow-hidden">
            <div class="bg-gradient-to-r from-rose-50 to-pink-50 px-6 py-4 border-b border-rose-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Metas Predeterminadas</h2>
                        <p class="text-sm text-gray-600">{{ $metas->count() }} registros</p>
                    </div>
                    <button onclick="openMetaModal()" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar
                    </button>
                </div>
            </div>
            
            <div class="p-6 max-h-[600px] overflow-y-auto">
                @forelse($metas as $meta)
                    <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg mb-3 hover:bg-gray-100 transition-colors">
                        <p class="text-gray-800 flex-1">{{ $meta->description }}</p>
                        <form action="{{ route('admin.panel.catalogos.metas.destroy', $meta->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta meta predeterminada?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-4 text-red-600 hover:text-red-800 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-gray-500">No hay metas predeterminadas registradas</p>
                        <p class="text-sm text-gray-400 mt-1">Haz clic en "Agregar" para crear una</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Objetivos Específicos Predeterminados --}}
        <div class="bg-white rounded-2xl shadow-lg border border-indigo-100 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-indigo-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Objetivos Específicos Predeterminados</h2>
                        <p class="text-sm text-gray-600">{{ $objetivos->count() }} registros</p>
                    </div>
                    <button onclick="openObjetivoModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar
                    </button>
                </div>
            </div>
            
            <div class="p-6 max-h-[600px] overflow-y-auto">
                @forelse($objetivos as $objetivo)
                    <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg mb-3 hover:bg-gray-100 transition-colors">
                        <p class="text-gray-800 flex-1">{{ $objetivo->description }}</p>
                        <form action="{{ route('admin.panel.catalogos.objetivos.destroy', $objetivo->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este objetivo específico?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-4 text-red-600 hover:text-red-800 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-gray-500">No hay objetivos específicos registrados</p>
                        <p class="text-sm text-gray-400 mt-1">Haz clic en "Agregar" para crear uno</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

{{-- Modal para Agregar Meta --}}
<div id="metaModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4">
        <div class="bg-gradient-to-r from-rose-600 to-pink-600 px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">Agregar Meta Predeterminada</h3>
        </div>
        <form action="{{ route('admin.panel.catalogos.metas.store') }}" method="POST" class="p-6">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                <textarea name="description" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="Ingrese la descripción de la meta predetermin ada..."></textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeMetaModal()" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-semibold transition-colors">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal para Agregar Objetivo --}}
<div id="objetivoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">Agregar Objetivo Específico Predeterminado</h3>
        </div>
        <form action="{{ route('admin.panel.catalogos.objetivos.store') }}" method="POST" class="p-6">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                <textarea name="description" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Ingrese la descripción del objetivo específico..."></textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeObjetivoModal()" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openMetaModal() {
    document.getElementById('metaModal').classList.remove('hidden');
    document.getElementById('metaModal').classList.add('flex');
}

function closeMetaModal() {
    document.getElementById('metaModal').classList.add('hidden');
    document.getElementById('metaModal').classList.remove('flex');
}

function openObjetivoModal() {
    document.getElementById('objetivoModal').classList.remove('hidden');
    document.getElementById('objetivoModal').classList.add('flex');
}

function closeObjetivoModal() {
    document.getElementById('objetivoModal').classList.add('hidden');
    document.getElementById('objetivoModal').classList.remove('flex');
}

// Close modals when clicking outside
document.getElementById('metaModal').addEventListener('click', function(e) {
    if (e.target === this) closeMetaModal();
});

document.getElementById('objetivoModal').addEventListener('click', function(e) {
    if (e.target === this) closeObjetivoModal();
});
</script>

@endsection
