@extends('layouts.poa')

@section('content')
<div class="max-w-5xl mx-auto pb-10">
    <x-poa.steps :paso="1" />

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-8">
            <div class="mb-6 border-b border-gray-100 pb-4">
                <h2 class="text-xl font-bold text-congress-blue-800">Definición del Proyecto</h2>
                <p class="text-sm text-gray-500">Ingrese los datos generales para identificar el POA.</p>
            </div>

            <form action="{{ route('poa.wizard.storeStep1', $proyecto->id ?? null) }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Nombre --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Proyecto <span class="text-gray-400 font-normal">(Opcional)</span></label>
                        <div class="relative">
                            <input type="text" name="nombre" id="nombre-input" maxlength="200"
                                value="{{ old('nombre', $proyecto->nombre ?? '') }}"
                                placeholder="Ej: Fortalecimiento Administrativo..."
                                class="w-full border-gray-300 rounded-lg px-4 py-2.5 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200 transition-all text-gray-800">
                            <div class="absolute right-3 top-3 text-xs text-gray-400"><span id="nombre-count">0</span>/200</div>
                        </div>
                    </div>

                    {{-- Año --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Año Fiscal</label>
                        <input type="number" name="anio"
                            value="{{ old('anio', $proyecto->anio ?? date('Y')) }}"
                            class="w-full border-gray-300 rounded-lg px-4 py-2.5 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200 transition-all font-bold text-center bg-gray-50" min="2020" max="2100" required>
                    </div>
                </div>

                {{-- Objetivo --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Objetivo de la Unidad</label>
                    <div class="relative">
                        <textarea name="objetivo_unidad" id="objetivo-input" maxlength="1000" rows="5"
                                class="w-full border-gray-300 rounded-lg px-4 py-3 focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200 transition-all text-gray-800 resize-none">{{ old('objetivo_unidad', $proyecto->objetivo_unidad ?? '') }}</textarea>
                        <div class="absolute right-3 bottom-3 text-xs text-gray-400"><span id="objetivo-count">0</span>/1000</div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="btn bg-congress-blue-700 hover:bg-congress-blue-800 text-white border-0 px-8 rounded-full shadow-lg">
                        Guardar y Continuar
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    ['nombre', 'objetivo'].forEach(id => {
        const input = document.getElementById(id + '-input');
        const count = document.getElementById(id + '-count');
        if(input && count) {
            input.addEventListener('input', () => count.textContent = input.value.length);
            count.textContent = input.value.length;
        }
    });
</script>
@endpush
@endsection
