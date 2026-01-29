@extends('layouts.poa')

@section('content')
<div class="space-y-6 pb-10 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-end gap-4 pb-2">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Panel Avanzado de Administración
            </h1>
            <p class="text-gray-500 mt-1 font-medium">Gestión centralizada del Sistema POA</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn bg-white hover:bg-gray-50 text-congress-blue-700 border border-congress-blue-200 shadow-sm px-6 rounded-xl font-bold transition-transform hover:-translate-y-0.5 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Dashboard
        </a>
    </div>

    {{-- Bienvenida --}}
    <div class="bg-gradient-to-r from-congress-blue-50 to-blue-50 rounded-2xl border border-congress-blue-100 p-8">
        <div class="flex items-center gap-4">
            <div class="p-4 bg-congress-blue-100 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-congress-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-congress-blue-900">Gestión de Tablas del Sistema</h2>
                <p class="text-congress-blue-700 mt-1">Selecciona una tabla para administrar sus registros</p>
            </div>
        </div>
    </div>

    {{-- Tablas Disponibles --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {{-- Proyectos --}}
        @php
            $totalProyectos = \App\Models\PoaProyecto::count();
        @endphp
        <a href="{{ route('admin.panel.proyectos') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border-2 border-blue-100 hover:border-blue-300 p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-blue-50 rounded-xl group-hover:bg-blue-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-blue-600">{{ $totalProyectos }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Proyectos POA</h3>
            <p class="text-sm text-gray-600 mb-4">Gestiona todos los proyectos del sistema</p>
            <div class="flex items-center text-blue-600 font-semibold text-sm group-hover:gap-2 transition-all">
                <span>Administrar</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        {{-- Usuarios --}}
        @php
            $totalUsuarios = \App\Models\User::count();
        @endphp
        <a href="{{ route('admin.panel.usuarios') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border-2 border-purple-100 hover:border-purple-300 p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-purple-50 rounded-xl group-hover:bg-purple-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-purple-600">{{ $totalUsuarios }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Usuarios</h3>
            <p class="text-sm text-gray-600 mb-4">Administra usuarios y roles del sistema</p>
            <div class="flex items-center text-purple-600 font-semibold text-sm group-hover:gap-2 transition-all">
                <span>Administrar</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        {{-- Unidades --}}
        @php
            $totalUnidades = \App\Models\Unidad::count();
        @endphp
        <a href="{{ route('admin.panel.unidades') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border-2 border-orange-100 hover:border-orange-300 p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-orange-50 rounded-xl group-hover:bg-orange-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-orange-600">{{ $totalUnidades }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Unidades</h3>
            <p class="text-sm text-gray-600 mb-4">Gestiona unidades organizacionales</p>
            <div class="flex items-center text-orange-600 font-semibold text-sm group-hover:gap-2 transition-all">
                <span>Administrar</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        {{-- Metas --}}
        @php
            $totalMetas = \App\Models\PoaMeta::count();
        @endphp
        <a href="{{ route('admin.panel.metas') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border-2 border-green-100 hover:border-green-300 p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-green-50 rounded-xl group-hover:bg-green-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-green-600">{{ $totalMetas }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Metas POA</h3>
            <p class="text-sm text-gray-600 mb-4">Gestiona metas de proyectos POA</p>
            <div class="flex items-center text-green-600 font-semibold text-sm group-hover:gap-2 transition-all">
                <span>Administrar</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        {{-- Actividades --}}
        @php
            $totalActividades = \App\Models\PoaActividad::count();
        @endphp
        <a href="{{ route('admin.panel.actividades') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border-2 border-teal-100 hover:border-teal-300 p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-teal-50 rounded-xl group-hover:bg-teal-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <span class="text-3xl font-bold text-teal-600">{{ $totalActividades }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Actividades POA</h3>
            <p class="text-sm text-gray-600 mb-4">Administra actividades de metas</p>
            <div class="flex items-center text-teal-600 font-semibold text-sm group-hover:gap-2 transition-all">
                <span>Administrar</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

    </div>

</div>
@endsection
