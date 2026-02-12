@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">
    
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Administración Central - Alcaldia Municipal de Santa Ana Centro
        </h1>
        <p class="text-gray-500 mt-1 font-medium">Panel de control del sistema POA</p>
    </div>
            
            {{-- Bienvenida --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold text-congress-blue-700 mb-2">¡Bienvenido, Administrador!</h3>
                    <p class="text-gray-600">Aquí podrás gestionar todas las unidades y proyectos del sistema POA.</p>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $totalProyectos = \App\Models\PoaProyecto::count();
                    $totalMetas = \App\Models\PoaMeta::count();
                    $totalActividades = \App\Models\PoaActividad::count();
                    $totalUnidades = \App\Models\Unidad::count();
                @endphp

                {{-- Proyectos --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Proyectos</p>
                            <p class="text-3xl font-bold text-congress-blue-600 mt-1">{{ $totalProyectos }}</p>
                        </div>
                        <div class="p-3 bg-congress-blue-50 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-congress-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Metas --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Metas</p>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ $totalMetas }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Actividades --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Actividades</p>
                            <p class="text-3xl font-bold text-purple-600 mt-1">{{ $totalActividades }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Unidades --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Unidades</p>
                            <p class="text-3xl font-bold text-orange-600 mt-1">{{ $totalUnidades }}</p>
                        </div>
                        <div class="p-3 bg-orange-50 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel Avanzado Button --}}
            <div class="bg-gradient-to-r from-congress-blue-600 to-congress-blue-700 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <h3 class="text-2xl font-bold mb-2">Panel Avanzado de Administración</h3>
                            <p class="text-congress-blue-100">Gestiona proyectos, metas, actividades, unidades y usuarios con control total.</p>
                        </div>
                        <a href="{{route('admin.panel.index')}}" class="btn bg-white hover:bg-gray-50 text-congress-blue-700 border-0 shadow-xl px-8 rounded-xl font-bold transition-transform hover:-translate-y-1 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Abrir Panel Avanzado
                        </a>
                    </div>
                </div>
            </div>

            {{-- Gestionar Unidades Card --}}
            @php
                $totalUnidadesActivas = \App\Models\User::where('role', 'unidad')->count();
            @endphp
            <div class="bg-gradient-to-r from-emerald-600 to-green-700 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <h3 class="text-2xl font-bold mb-2">Gestionar Unidades</h3>
                            <p class="text-emerald-100">Visualiza el rendimiento de las unidades, revisa sus proyectos y aprueba o rechaza POAs.</p>
                            <p class="text-emerald-200 mt-2 text-sm">{{ $totalUnidadesActivas }} unidades activas</p>
                        </div>
                        <a href="{{ route('admin.unidades.index') }}" class="btn bg-white hover:bg-gray-50 text-emerald-700 border-0 shadow-xl px-8 rounded-xl font-bold transition-transform hover:-translate-y-1 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Gestionar Unidades
                        </a>
                    </div>
                </div>
            </div>

            {{-- Catálogos Predeterminados Card --}}
            @php
                $totalCatalogos = \App\Models\MetaPredeterminada::count() + \App\Models\ObjetivoEspecificoPredeterminado::count();
            @endphp
            <div class="bg-gradient-to-r from-rose-600 to-pink-700 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <h3 class="text-2xl font-bold mb-2">Metas y Objetivos Predeterminados</h3>
                            <p class="text-rose-100">Gestiona catálogos de opciones predeterminadas para el Wizard de Proyectos.</p>
                            <p class="text-rose-200 mt-2 text-sm">{{ $totalCatalogos }} registros totales</p>
                        </div>
                        <a href="{{ route('admin.panel.catalogos') }}" class="btn bg-white hover:bg-gray-50 text-rose-700 border-0 shadow-xl px-8 rounded-xl font-bold transition-transform hover:-translate-y-1 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Gestionar Catálogos
                        </a>
                    </div>
                </div>
            </div>

            {{-- Estadísticas Card --}}
            <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <h3 class="text-2xl font-bold mb-2">Estadísticas</h3>
                            <p class="text-purple-100">Visualiza el cumplimiento general de las unidades, rankings y gráficos de desempeño.</p>
                            <p class="text-purple-200 mt-2 text-sm">Análisis global</p>
                        </div>
                        <a href="{{ route('admin.statistics.index') }}" class="btn bg-white hover:bg-gray-50 text-purple-700 border-0 shadow-xl px-8 rounded-xl font-bold transition-transform hover:-translate-y-1 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Ver Estadísticas
                        </a>
                    </div>
                </div>
            </div>

</div>
@endsection
