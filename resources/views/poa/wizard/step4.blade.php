@extends('layouts.poa')

@section('content')
<div class="max-w-7xl mx-auto pb-24">
    <x-poa.steps :paso="4" />

    {{-- Header mejorado --}}
    <div class="bg-gradient-to-r from-congress-blue-700 to-congress-blue-800 rounded-2xl shadow-lg mb-6 p-6">
        <div class="text-white">
            <h2 class="text-2xl font-bold mb-2">Cronograma de Actividades</h2>
            <p class="text-congress-blue-100">Distribuye las cantidades programadas de cada actividad a lo largo de los 12 meses del año.</p>
        </div>
    </div>

    <form action="{{ route('poa.wizard.storeProgramacion', $proyecto->id) }}" method="POST" id="mainForm">
        @csrf

        <div class="space-y-6">
            @foreach($proyecto->metas as $meta)
                <div class="bg-white rounded-2xl shadow-md border-2 border-gray-100 overflow-hidden hover:shadow-xl transition-shadow">
                    {{-- Header Meta mejorado --}}
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b-2 border-gray-200 flex items-center gap-3">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-congress-blue-600 text-white text-sm font-bold shadow-sm">{{ $loop->iteration }}</span>
                        <h3 class="text-base font-bold text-gray-800">{{ $meta->descripcion }}</h3>
                        <span class="ml-auto text-xs bg-white px-3 py-1 rounded-full text-gray-600 font-semibold">
                            {{ $meta->actividades->where('es_cuantificable', true)->count() }} actividades
                        </span>
                    </div>

                    <div class="p-6">
                        @forelse($meta->actividades->where('es_cuantificable', true) as $actividad)
                            <div class="mb-8 last:mb-0">
                                {{-- Cabecera de actividad mejorada --}}
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-4 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-bold text-gray-900 mb-2">{{ $actividad->descripcion }}</h4>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold">
                                                {{ $actividad->unidad_medida }}
                                            </span>
                                            <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">
                                                Meta Total: <strong id="total-target-{{$actividad->id}}">{{ $actividad->cantidad_programada_total }}</strong>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 bg-white p-3 rounded-lg border-2 border-gray-200 shadow-sm">
                                        <div class="text-right px-2">
                                            <div class="text-[10px] uppercase text-gray-500 font-bold mb-1">Total Programado</div>
                                            <div id="current-sum-{{ $actividad->id }}" class="text-lg font-mono font-bold text-gray-400">0</div>
                                        </div>
                                        <button type="button" 
                                                class="btn btn-sm bg-white hover:bg-congress-blue-50 text-congress-blue-600 border-2 border-congress-blue-400 hover:border-congress-blue-600 shadow-sm font-semibold gap-1 transition-all"
                                                onclick="distributeEvenly({{ $actividad->id }}, {{ $actividad->cantidad_programada_total }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Repartir Uniformemente
                                        </button>

                                    </div>
                                </div>

                                {{-- Grid de meses mejorado --}}
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-12 gap-3">
                                    @foreach($actividad->programaciones->sortBy('mes') as $prog)
                                        <div class="relative group">
                                            <label class="block text-[10px] text-center text-gray-500 mb-2 uppercase font-bold tracking-wide">
                                                {{ substr(\Carbon\Carbon::create()->month($prog->mes)->locale('es')->monthName, 0, 3) }}
                                            </label>
                                            <input type="number"
                                                   name="programacion[{{ $actividad->id }}][{{ $prog->mes }}]"
                                                   value="{{ $prog->cantidad_programada + 0 }}"
                                                   class="w-full text-center text-sm font-semibold border-2 border-gray-300 rounded-lg focus:border-congress-blue-500 focus:ring-2 focus:ring-congress-blue-200 py-2.5 month-input-{{ $actividad->id }} transition-all hover:border-gray-400"
                                                   step="1" 
                                                   min="0"
                                                   oninput="validateSum({{ $actividad->id }})">
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Mensaje de error mejorado --}}
                                <div id="error-msg-{{ $actividad->id }}" class="hidden mt-3 p-3 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm text-red-700 font-semibold">
                                            La suma debe ser exactamente igual a la meta total
                                        </span>
                                    </div>
                                </div>

                                @if(!$loop->last) 
                                    <hr class="my-6 border-t-2 border-gray-200"> 
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="font-medium">No hay actividades cuantificables en esta meta</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- BARRA PEGAJOSA (Sticky Footer) mejorada --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gray-300 p-4 z-50 shadow-2xl">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <a href="{{ route('poa.wizard.step3', $proyecto->id) }}" 
                   class="flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Actividades
                </a>

                <button type="submit" 
                        class="btn bg-congress-blue-700 hover:bg-congress-blue-800 text-white border-0 px-8 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2">
                    <span class="font-bold">Finalizar Programación</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function distributeEvenly(id, total) {
        const inputs = document.querySelectorAll(`.month-input-${id}`);
        const base = Math.floor(total / 12);
        const remainder = total % 12;
        inputs.forEach((input, index) => {
            input.value = base + (index < remainder ? 1 : 0);
        });
        validateSum(id);
    }

    function validateSum(id) {
        const inputs = document.querySelectorAll(`.month-input-${id}`);
        const target = parseInt(document.getElementById(`total-target-${id}`).innerText);
        let sum = 0;
        inputs.forEach(inp => sum += (parseInt(inp.value) || 0));

        const sumSpan = document.getElementById(`current-sum-${id}`);
        const errorMsg = document.getElementById(`error-msg-${id}`);

        sumSpan.innerText = sum;

        if(sum !== target) {
            sumSpan.classList.add('text-red-600'); 
            sumSpan.classList.remove('text-congress-blue-600');
            errorMsg.classList.remove('hidden');
        } else {
            sumSpan.classList.remove('text-red-600'); 
            sumSpan.classList.add('text-congress-blue-600');
            errorMsg.classList.add('hidden');
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const inputs = document.querySelectorAll('input[class*="month-input-"]');
        const ids = new Set();
        inputs.forEach(i => ids.add(i.className.match(/month-input-(\d+)/)[1]));
        ids.forEach(id => validateSum(id));
    });
</script>
@endpush
@endsection
