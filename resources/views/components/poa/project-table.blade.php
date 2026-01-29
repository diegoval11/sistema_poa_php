@props(['proyectos'])

<div class="overflow-hidden bg-white rounded-2xl border border-gray-200 shadow-sm">
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                    <th class="py-4 pl-6">Proyecto</th>
                    <th class="py-4">Año</th>
                    <th class="py-4">Estado</th>
                    <th class="py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($proyectos as $proyecto)
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    {{-- Columna: Nombre --}}
                    <td class="pl-6 py-4">
    {{-- Si hay nombre, lo muestra. Si es NULL, muestra 'Sin Título' en cursiva y gris --}}
    <div class="{{ $proyecto->nombre ? 'text-congress-blue-900 font-bold' : 'text-gray-400 italic font-medium' }} text-sm">
        {{ $proyecto->nombre ?? 'Sin Título' }}
    </div>
    <div class="text-xs text-gray-500 truncate max-w-xs mt-0.5">
        {{ Str::limit($proyecto->objetivo_unidad ?? 'Sin objetivo definido', 60) }}
    </div>
</td>
                    </td>

                    {{-- Columna: Año --}}
                    <td class="py-4 font-medium text-gray-600 text-sm">{{ $proyecto->anio }}</td>

                    {{-- Columna: Estado --}}
                    <td class="py-4">
                        @php
                            $badges = [
                                'BORRADOR' => 'bg-gray-100 text-gray-500 border-gray-200',
                                'ENVIADO'  => 'bg-orange-50 text-orange-600 border-orange-100',
                                'APROBADO' => 'bg-green-50 text-green-600 border-green-100',
                                'RECHAZADO'=> 'bg-red-50 text-red-600 border-red-100',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badges[$proyecto->estado] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ ucfirst(strtolower($proyecto->estado)) }}
                        </span>
                    </td>

                    {{-- Columna: Acciones --}}
                    <td class="py-4 text-center">
                        <div class="flex justify-center items-center gap-3">

                            @switch($proyecto->estado)

                                {{-- CASO 1: BORRADOR (Full Control) --}}
                                @case('BORRADOR')
                                    {{-- A. ENVIAR --}}
                                    <button onclick="openModal('{{ route('poa.wizard.sendProject', $proyecto->id) }}', 'send')"
                                            class="h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm flex items-center gap-2 transition-all active:scale-95"
                                            title="Enviar proyecto a revisión">
                                        <span class="text-xs font-bold tracking-wide">ENVIAR</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                    </button>

                                    <div class="w-px h-5 bg-gray-200"></div>

                                    {{-- B. EDITAR --}}
                                    <a href="{{ route('poa.wizard.step1', $proyecto->id) }}"
                                       class="h-9 w-9 flex justify-center items-center rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 hover:border-indigo-200 transition-colors"
                                       title="Editar información">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- C. ELIMINAR --}}
                                    <button onclick="openModal('{{ route('poa.wizard.deleteProject', $proyecto->id) }}', 'delete')"
                                            class="h-9 w-9 flex justify-center items-center rounded-lg bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 hover:border-red-200 transition-colors"
                                            title="Eliminar proyecto permanentemente">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @break

                                {{-- CASO 2: ENVIADO (Bloqueado) --}}
                                @case('ENVIADO')
                                    <div class="h-9 px-4 rounded-lg bg-slate-100 border border-slate-200 text-slate-500 flex items-center gap-2 cursor-not-allowed w-full justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span class="text-xs font-bold uppercase">En Revisión</span>
                                    </div>
                                    @break

                                {{-- CASO 3: RECHAZADO (Solo Eliminar) --}}
                                @case('RECHAZADO')
                                     <button onclick="openModal('{{ route('poa.wizard.deleteProject', $proyecto->id) }}', 'delete')"
                                            class="h-9 px-4 bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 rounded-lg flex items-center gap-2 transition-all w-full justify-center">
                                        <span class="text-xs font-bold">ELIMINAR</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @break

                                {{-- CASO 4: APROBADO (Gestionar) --}}
                                @case('APROBADO')
                                    <a href="{{ route('poa.avances.index', $proyecto->id) }}" class="h-9 px-4 bg-emerald-600 text-white hover:bg-emerald-700 rounded-lg flex items-center gap-2 transition-all shadow-sm w-full justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <span class="text-xs font-bold">AVANCES</span>
                                    </a>
                                    @break

                            @endswitch

                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
