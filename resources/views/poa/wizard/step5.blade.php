@extends('layouts.poa')

@section('content')
<div class="max-w-4xl mx-auto pb-10">
    <x-poa.steps :paso="5" :proyectoId="$proyecto->id" />

    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden relative">
        <div class="h-1.5 bg-congress-blue-600 w-full"></div>

        <div class="p-8 md:p-12">
            {{-- Encabezado Documento --}}
            <div class="border-b border-gray-100 pb-6 mb-8 flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $proyecto->nombre }}</h2>
                    <div class="flex gap-2 mt-2 text-sm text-gray-500">
                        <span class="font-bold text-congress-blue-700">Año {{ $proyecto->anio }}</span>
                        <span>•</span>
                        <span>{{ Auth::user()->unidad?->nombre ?? 'Unidad' }}</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="block text-xs uppercase text-gray-400 font-bold">Actividades</span>
                    <span class="text-3xl font-light text-gray-800">{{ $totalActividades }}</span>
                </div>
            </div>

            {{-- Objetivo --}}
            <div class="mb-8">
                <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Objetivo</h3>
                <p class="text-gray-700 italic bg-gray-50 p-4 rounded border border-gray-100">"{{ $proyecto->objetivo_unidad }}"</p>
            </div>

            {{-- Detalle --}}
            <div class="space-y-6">
                @foreach($proyecto->metas as $meta)
                    <div>
                        <div class="flex gap-3 mb-2">
                            <span class="font-bold text-congress-blue-600">0{{ $loop->iteration }}.</span>
                            <h4 class="font-bold text-gray-800">{{ $meta->descripcion }}</h4>
                        </div>
                        <ul class="pl-8 list-disc list-inside text-sm text-gray-600 space-y-1">
                            @foreach($meta->actividades as $actividad)
                                <li>
                                    {{ $actividad->descripcion }}
                                    @if($actividad->es_cuantificable)
                                        <span class="font-bold text-gray-800 ml-1">({{ $actividad->cantidad_programada_total }} {{ $actividad->unidad_medida }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 text-xs text-gray-400 flex justify-between">
            <span>Borrador generado automáticamente</span>
            <span>{{ date('d/m/Y') }}</span>
        </div>
    </div>

    <div class="mt-8 flex justify-between items-center">
        <a href="{{ route('poa.wizard.step4', $proyecto->id) }}" class="text-gray-500 hover:text-gray-900 font-medium">← Volver</a>

        <form action="{{ route('poa.wizard.finish', $proyecto->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0 rounded-full px-8 shadow-lg transform hover:-translate-y-0.5 transition-transform">
                Confirmar y Finalizar POA
            </button>
        </form>
    </div>
</div>
@endsection
