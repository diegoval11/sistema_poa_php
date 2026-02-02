@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">
    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-congress-blue-700">{{ $titulo }}</h1>
            <p class="text-gray-500 mt-1">Análisis detallado del desempeño</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Dashboard
        </a>
    </div>

    {{-- Tabs Navigation --}}
    <div class="flex items-end gap-1 px-2 overflow-x-auto">
        <div onclick="switchTab('cumplimiento')" id="tab-cumplimiento" class="px-6 py-3 font-semibold border-t border-x rounded-t-lg cursor-pointer transition-all relative -mb-px z-10 bg-white text-congress-blue-700 border-gray-200 border-b-white shadow-[0_-2px_4px_rgba(0,0,0,0.05)]">
            Cumplimiento por Unidad
        </div>
        <div onclick="switchTab('estado')" id="tab-estado" class="px-6 py-3 font-semibold border-t border-x rounded-t-lg cursor-pointer transition-all relative -mb-px z-0 bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-50 hover:text-congress-blue-600">
            Estado de Proyectos
        </div>
        <div onclick="switchTab('avances')" id="tab-avances" class="px-6 py-3 font-semibold border-t border-x rounded-t-lg cursor-pointer transition-all relative -mb-px z-0 bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-50 hover:text-congress-blue-600">
            Avances Mensuales
        </div>
        <div onclick="switchTab('trimestral')" id="tab-trimestral" class="px-6 py-3 font-semibold border-t border-x rounded-t-lg cursor-pointer transition-all relative -mb-px z-0 bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-50 hover:text-congress-blue-600">
            Trimestral
        </div>
    </div>

    {{-- Tabs Content Container --}}
    <div class="bg-white border border-gray-200 rounded-b-lg rounded-tr-lg shadow-sm p-8 relative z-0 min-h-[500px]" style="border-top-left-radius: 0;">
        
        {{-- Tab 1: Cumplimiento por Unidad --}}
        <div id="cumplimiento" class="tab-pane block space-y-8">
            <div class="grid grid-cols-1 gap-8">
                {{-- Top Units --}}
                <div class="card bg-base-100 shadow-xl border border-gray-100">
                    <div class="card-body">
                        <h2 class="card-title text-success mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            Mayor Desempeño
                        </h2>
                        <div class="h-96 w-full">
                            <canvas id="topChart"></canvas>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        {{-- Tab 2: Estado de Proyectos --}}
        <div id="estado" class="tab-pane hidden">
            <div class="card bg-base-100 shadow-xl border border-gray-100 max-w-3xl mx-auto">
                <div class="card-body">
                    <h2 class="card-title text-congress-blue-700 mb-4">Distribución de Proyectos</h2>
                    <div class="h-96 relative w-full">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 3: Avances Mensuales --}}
        <div id="avances" class="tab-pane hidden">
            <div class="card bg-base-100 shadow-xl border border-gray-100">
                <div class="card-body">
                    <h2 class="card-title text-congress-blue-700 mb-4">Promedio de Cumplimiento Mensual</h2>
                    <p class="text-sm text-gray-500 mb-4">Promedio del porcentaje de cumplimiento de todas las actividades por mes.</p>
                    <div class="h-96 w-full">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 4: Trimestral --}}
        <div id="trimestral" class="tab-pane hidden">
            
            {{-- Búsqueda y Exportaciones --}}
            <div class="flex flex-wrap gap-3 justify-between mb-6">
                {{-- Buscador --}}
                <form method="GET" action="{{ route('admin.statistics.index') }}" class="flex gap-2">
                    <input type="hidden" name="tab" value="trimestral">
                    <div class="form-control">
                        <input type="text" name="buscar" value="{{ $busqueda }}" placeholder="Buscar unidad..." class="input input-bordered w-64" />
                    </div>
                    <button type="submit" class="btn bg-congress-blue-600 hover:bg-congress-blue-700 text-white border-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Buscar
                    </button>
                </form>

                {{-- Botones de Exportación --}}
                <div class="flex gap-2">
                    <a href="{{ route('admin.statistics.export.excel') }}" class="btn bg-green-600 hover:bg-green-700 text-white border-0 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar Excel
                    </a>
                    <a href="{{ route('admin.statistics.export.pdf') }}" class="btn bg-red-600 hover:bg-red-700 text-white border-0 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Exportar PDF
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                @forelse($unidadesTrimestral as $unidad)
                <div class="card bg-base-100 shadow-md hover:shadow-lg transition-shadow border border-gray-100">
                    <div class="card-body p-6">
                        <h3 class="text-lg font-bold text-congress-blue-800 mb-4">{{ $unidad->unidad->nombre ?? $unidad->name }}</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($unidad->quarters as $q => $val)
                            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Trimestre {{ $q }}</div>
                                <div class="text-2xl font-black {{ $val >= 80 ? 'text-green-600' : ($val >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $val }}%
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-500">
                    No se encontraron unidades.
                </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            <div class="mt-8 flex justify-center">
                {{ $unidadesTrimestral->appends(['tab' => 'trimestral', 'buscar' => $busqueda])->links() }}
            </div>
        </div>

    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data
    const topUnits = @json($topUnits);
    const bottomUnits = @json($bottomUnits);
    const proyectosData = @json($proyectosData);
    const avancesMensuales = @json($avancesMensuales);

    // Chart Instances
    let charts = {};

    // Common Options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
    };

    // Initialization Functions
    function initCumplimientoCharts() {
        if (charts.top) return; // Already initialized

        const barOptions = {
            ...commonOptions,
            indexAxis: 'y',
            scales: { x: { beginAtZero: true, max: 100 } },
            plugins: { legend: { display: false } }
        };

        charts.top = new Chart(document.getElementById('topChart'), {
            type: 'bar',
            data: {
                labels: topUnits.map(u => u.nombre),
                datasets: [{
                    data: topUnits.map(u => u.cumplimiento),
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: barOptions
        });


    }

    function initStatusChart() {
        if (charts.status) return;

        charts.status = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Aprobado', 'Enviado', 'Borrador', 'Rechazado'],
                datasets: [{
                    data: proyectosData,
                    backgroundColor: ['#10b981', '#f59e0b', '#9ca3af', '#ef4444'],
                    borderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    function initMonthlyChart() {
        if (charts.monthly) return;

        charts.monthly = new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Cumplimiento Promedio (%)',
                    data: avancesMensuales,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                ...commonOptions,
                scales: { y: { beginAtZero: true, max: 100 } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // Tab Switching Logic
    function switchTab(tabId) {
        // UI Updates
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('block'));
        document.getElementById(tabId).classList.remove('hidden');
        document.getElementById(tabId).classList.add('block');
        
        // Reset all tabs to inactive state
        const tabs = ['cumplimiento', 'estado', 'avances', 'trimestral'];
        const inactiveClasses = ['bg-gray-100', 'text-gray-500', 'border-gray-200', 'z-0', 'hover:bg-gray-50', 'hover:text-congress-blue-600'];
        const activeClasses = ['bg-white', 'text-congress-blue-700', 'border-b-white', 'z-10', 'shadow-[0_-2px_4px_rgba(0,0,0,0.05)]'];

        tabs.forEach(t => {
            const el = document.getElementById('tab-' + t);
            if (t === tabId) {
                el.classList.remove(...inactiveClasses);
                el.classList.add(...activeClasses);
                el.classList.add('border-gray-200'); // Keep border except bottom
            } else {
                el.classList.remove(...activeClasses);
                el.classList.add(...inactiveClasses);
            }
        });

        // Lazy Load Charts
        if (tabId === 'cumplimiento') initCumplimientoCharts();
        if (tabId === 'estado') initStatusChart();
        if (tabId === 'avances') initMonthlyChart();

        // Update URL
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    // Initialize on Load
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'cumplimiento';
        switchTab(activeTab);
    });
</script>
@endsection
