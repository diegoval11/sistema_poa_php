@props(['paso'])

@php
    $steps = [
        1 => 'Información',
        2 => 'Metas',
        3 => 'Actividades',
        4 => 'Programación',
        5 => 'Resumen'
    ];

    $totalSteps = count($steps);
    $progressPercent = ($paso - 1) / ($totalSteps - 1) * 100;
    $progressPercent = min(100, max(0, $progressPercent));
@endphp

<div class="w-full max-w-5xl mx-auto mb-10">
    {{-- CONTENEDOR DESTACADO (Bloque Azul) --}}
    <div class="bg-congress-blue-800 rounded-2xl shadow-xl overflow-hidden relative p-6 md:p-8">

        {{-- LÍNEAS DE FONDO --}}
        <div class="absolute top-1/2 left-0 w-full transform -translate-y-[60%] px-12 md:px-16 z-0">
            <div class="w-full h-1.5 bg-congress-blue-600/40 rounded-full"></div>
            <div class="absolute top-0 left-0 h-1.5 bg-white rounded-full transition-all duration-700 ease-out shadow-[0_0_12px_rgba(255,255,255,0.6)]"
                 style="width: {{ $progressPercent }}%; margin-left: 3rem; margin-right: 3rem;"></div>
        </div>

        {{-- PASOS --}}
        <div class="relative flex justify-between items-start w-full z-10 px-2 md:px-6">
            @foreach ($steps as $num => $label)
                @php
                    $isCompleted = $paso > $num;
                    $isCurrent = $paso == $num;
                    $isFuture = !$isCompleted && !$isCurrent;
                @endphp

                <div class="flex flex-col items-center group {{ $isFuture ? 'opacity-70' : '' }}" style="width: 80px;">
                    <div class="w-12 h-12 flex items-center justify-center rounded-full border-[3px] transition-all duration-500 ease-in-out mb-3 relative
                        {{ $isCompleted ? 'bg-white border-white text-congress-blue-800 scale-100 shadow-md' : '' }}
                        {{ $isCurrent ? 'bg-white border-congress-blue-300 scale-115 ring-[6px] ring-congress-blue-500/30 shadow-[0_0_20px_rgba(255,255,255,0.4)] z-20' : '' }}
                        {{ $isFuture ? 'bg-congress-blue-900 border-congress-blue-600 text-congress-blue-300 scale-95' : '' }}
                    ">
                        @if($isCompleted)
                            <svg class="w-6 h-6 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <span class="text-lg font-black font-mono leading-none">{{ $num }}</span>
                        @endif

                        @if($isCurrent)
                            <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-1.5 h-1.5 bg-white rounded-full"></div>
                        @endif
                    </div>

                    <span class="text-sm font-bold tracking-wider text-center whitespace-nowrap
                        {{ $isCurrent ? 'text-white translate-y-1' : '' }}
                        {{ $isCompleted ? 'text-congress-blue-100' : '' }}
                        {{ $isFuture ? 'text-congress-blue-400 font-medium' : '' }}">
                        {{ $label }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
