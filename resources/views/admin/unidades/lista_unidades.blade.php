@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">
    {{-- Encabezado con buscador y exportaciones --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-congress-blue-700">{{ $titulo }}</h1>
            <p class="text-gray-500 mt-1">Gestión de unidades y sus proyectos</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            {{-- Buscador dinámico --}}
            <div class="form-control w-full md:w-80">
                <input 
                    type="text" 
                    id="buscador-unidades"
                    placeholder="Buscar unidad..." 
                    class="input input-bordered w-full"
                    value="{{ $busqueda }}"
                />
            </div>
            
            {{-- Botones de Exportación --}}
            <div class="flex gap-2">
                <a href="{{ route('admin.unidades.export.excel') }}" class="btn bg-green-600 hover:bg-green-700 text-white border-0 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar Excel
                </a>
                <a href="{{ route('admin.unidades.export.pdf') }}" class="btn bg-red-600 hover:bg-red-700 text-white border-0 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Exportar PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Lista de unidades --}}
    <div id="contenedor-unidades" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($unidades as $unidad)
        <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
            <div class="card-body">
                <h2 class="card-title text-congress-blue-700">{{ $unidad->unidad->nombre }}</h2>
                <p class="text-sm text-gray-500">{{ $unidad->email }}</p>
                
                <div class="stats stats-vertical shadow mt-4">
                    <div class="stat py-2">
                        <div class="stat-title text-xs">Total Proyectos</div>
                        <div class="stat-value text-2xl text-congress-blue-600">{{ $unidad->total_proyectos }}</div>
                    </div>
                    <div class="stat py-2">
                        <div class="stat-title text-xs">Aprobados</div>
                        <div class="stat-value text-2xl text-success">{{ $unidad->count_proyectos_aprobados }}</div>
                    </div>
                    {{-- Indicador de rendimiento con colores --}}
                    <div class="stat py-2">
                        <div class="stat-title text-xs">Rendimiento</div>
                        @php
                            $colorClass = 'text-error';
                            if ($unidad->rendimiento >= 80) {
                                $colorClass = 'text-success';
                            } elseif ($unidad->rendimiento >= 60) {
                                $colorClass = 'text-warning';
                            } elseif ($unidad->rendimiento >= 40) {
                                $colorClass = 'text-orange-500';
                            }
                        @endphp
                        <div class="stat-value text-2xl {{ $colorClass }}">
                            {{ $unidad->rendimiento }}%
                        </div>
                        <div class="stat-desc">
                            @if($unidad->rendimiento >= 80)
                                <span class="badge badge-success badge-sm">Excelente</span>
                            @elseif($unidad->rendimiento >= 60)
                                <span class="badge badge-warning badge-sm">Bueno</span>
                            @elseif($unidad->rendimiento >= 40)
                                <span class="badge badge-warning badge-sm bg-orange-500 border-orange-500">Regular</span>
                            @else
                                <span class="badge badge-error badge-sm">Bajo</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('admin.unidades.proyectos', $unidad->id) }}" class="btn btn-primary btn-sm bg-congress-blue-600 hover:bg-congress-blue-700 border-none">
                        Ver Proyectos
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">No se encontraron unidades.</p>
        </div>
        @endforelse
    </div>
</div>

    {{-- Paginación --}}
    <div class="mt-6">
        {{ $unidades->appends(['buscar' => $busqueda])->links() }}
    </div>
</div>

<script>
    // Buscador dinámico de unidades
    const buscador = document.getElementById('buscador-unidades');
    let timeoutId;
    
    buscador.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const busqueda = this.value;
        
        timeoutId = setTimeout(() => {
            // Actualizar URL sin recargar la página
            const url = new URL(window.location);
            if (busqueda) {
                url.searchParams.set('buscar', busqueda);
            } else {
                url.searchParams.delete('buscar');
            }
            
            // CRITICAL: Reset pagination to page 1 when searching
            url.searchParams.delete('page');
            
            window.history.pushState({}, '', url);
            
            // Recargar la página con el nuevo parámetro
            window.location.href = url;
        }, 500);
    });
</script>
@endsection
