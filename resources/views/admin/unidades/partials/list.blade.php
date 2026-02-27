<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($unidades as $unidad)
    <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
        <div class="card-body">
            <h2 class="card-title text-congress-blue-700">{{ $unidad->unidad?->nombre ?? 'Sin nombre' }}</h2>
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

{{-- Paginación --}}
<div class="mt-6">
    {{ $unidades->appends(['buscar' => $busqueda])->links() }}
</div>
