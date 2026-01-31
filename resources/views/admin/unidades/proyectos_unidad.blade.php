@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-congress-blue-700">{{ $titulo }}</h1>
            <p class="text-gray-500 mt-1">{{ $unidad->email }}</p>
        </div>
        <div class="flex flex-col md:flex-row items-stretch md:items-end gap-3">
            <!-- Objetivo Estratégico Dropdown -->
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-semibold">Objetivo Estratégico <span class="text-error">*</span></span>
                </label>
                <select id="objetivo-estrategico" class="select select-bordered w-full md:w-80" required>
                    <option value="">Seleccione un objetivo...</option>
                    @foreach($objetivos as $objetivo)
                        <option value="{{ $objetivo->id }}">{{ Str::limit($objetivo->description, 80) }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Exportar POA Button -->
            <button id="btn-exportar-poa" class="btn btn-success gap-2" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar POA Excel
            </button>
            
            <a href="{{ route('admin.unidades.index') }}" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success shadow-lg mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-congress-blue-700">Proyectos</h2>

            @if($proyectos->count() > 0)
            <div class="alert alert-info mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Total de proyectos: {{ $proyectos->count() }}. Revise cada proyecto antes de aprobarlo.</span>
            </div>
            @endif

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Proyecto</th>
                            <th>Año</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($proyectos as $proyecto)
                        <tr>
                            <td>
                                <div class="font-semibold">
                                    @if($proyecto->nombre)
                                    {{ $proyecto->nombre }}
                                    @else
                                    <span class="italic text-gray-400">(Sin nombre)</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $proyecto->anio }}</td>
                            <td>
                                @if($proyecto->estado == 'BORRADOR')
                                <span class="badge badge-ghost">Borrador</span>
                                @elseif($proyecto->estado == 'ENVIADO')
                                <span class="badge badge-warning">Enviado</span>
                                @elseif($proyecto->estado == 'APROBADO')
                                <span class="badge badge-success">Aprobado</span>
                                @elseif($proyecto->estado == 'RECHAZADO')
                                <span class="badge badge-error">Rechazado</span>
                                @endif
                            </td>
                            <td>{{ $proyecto->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="flex gap-2 justify-center flex-wrap">
                                    <a href="{{ route('admin.proyectos.detalle', $proyecto->id) }}"
                                        class="btn btn-sm btn-info">
                                        Ver Detalles
                                    </a>

                                    @if($proyecto->estado == 'APROBADO')
                                    <span class="badge badge-success gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        POA Aprobado
                                    </span>
                                    @elseif($proyecto->estado == 'BORRADOR')
                                    <span class="badge badge-ghost">Esperando envío</span>
                                    @elseif($proyecto->estado == 'RECHAZADO')
                                    <span class="badge badge-error">Rechazado</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-8">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-50" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p>Esta unidad no tiene proyectos registrados.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    //JavaScript for objective selection and POA export
    const selectObjetivo = document.getElementById('objetivo-estrategico');
    const btnExportar = document.getElementById('btn-exportar-poa');
    
    selectObjetivo.addEventListener('change', function() {
        btnExportar.disabled = !this.value;
    });
    
    btnExportar.addEventListener('click', function() {
        const objetivoId = selectObjetivo.value;
        if (!objetivoId) {
            alert('Debe seleccionar un objetivo estratégico');
            return;
        }
        window.location.href = `{{ route('admin.unidades.exportar-poa', $unidad->id) }}?objetivo_estrategico_id=${objetivoId}`;
    });
</script>
@endsection
