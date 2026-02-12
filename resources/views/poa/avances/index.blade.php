@extends('layouts.poa')

@section('content')
<div class="max-w-6xl mx-auto pb-20">
    
    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Gestionar Avances</h1>
            <p class="text-gray-500 font-medium">Proyecto: {{ $proyecto->nombre }} ({{ $proyecto->anio }})</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 font-bold text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Dashboard
        </a>
    </div>

    {{-- RESUMEN DE CUMPLIMIENTO (VERTICAL) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-gray-900 rounded-xl shadow-lg border border-gray-800 p-6 flex flex-col justify-center items-center text-center">
            <span class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2">Cumplimiento Global</span>
            <span class="text-white font-black text-5xl">{{ $porcentajeGlobal }}%</span>
            <div class="w-full bg-gray-800 rounded-full h-2 mt-4 overflow-hidden">
                <div class="bg-white h-full transition-all duration-700" style="width: {{ $porcentajeGlobal }}%"></div>
            </div>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Desglose Mensual (Cálculo Estricto)</span>
                <span class="text-[10px] font-bold text-gray-400 italic">min(ejecutado, programado)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[11px] text-left">
                    <thead class="bg-gray-50 text-gray-400 uppercase font-black border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2">Mes</th>
                            <th class="px-4 py-2 text-center">Programado</th>
                            <th class="px-4 py-2 text-center">Ejec. Efectivo</th>
                            <th class="px-4 py-2 text-center">Cumplimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($statsMensuales as $mes => $data)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-2 font-bold text-gray-700">{{ ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][$mes] }}</td>
                                <td class="px-4 py-2 text-center text-gray-500">{{ $data['programado'] + 0 }}</td>
                                <td class="px-4 py-2 text-center font-bold text-gray-900">{{ $data['ejecutado_efectivo'] + 0 }}</td>
                                <td class="px-4 py-2 text-center">
                                    <span class="font-black {{ $data['porcentaje'] >= 100 ? 'text-green-600' : ($data['porcentaje'] > 0 ? 'text-yellow-600' : 'text-gray-300') }}">
                                        {{ $data['porcentaje'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Listado de Actividades --}}
    <div class="space-y-6">
        @php $globalActividadIndex = 0; @endphp
        @foreach($proyecto->metas as $meta)
            <div class="border-l-4 border-gray-900 pl-4 mb-4">
                <h2 class="text-gray-900 font-black text-sm uppercase tracking-widest">{{ $meta->descripcion }}</h2>
            </div>

            @foreach($meta->actividades as $actividad)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
                    {{-- Header Actividad (Clickable) --}}
                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center cursor-pointer hover:bg-gray-100 transition-colors border-b border-gray-200"
                         onclick="toggleActividad({{ $globalActividadIndex }})">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800 text-sm">{{ $actividad->descripcion }}</h3>
                            <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest mt-1">
                                Unidad: {{ $actividad->unidad_medida }} | 
                                @if($actividad->es_no_planificada)
                                    <span class="text-orange-600">No Planificada</span>
                                @else
                                    <span>Planificada</span>
                                @endif
                            </p>
                        </div>
                        <div class="text-right flex items-center gap-4">
                            <div>
                                <span class="text-[10px] font-black text-gray-400 uppercase block mb-1">Cumplimiento</span>
                                <span class="font-black text-lg text-gray-900">{{ $actividad->porcentaje_cumplimiento }}%</span>
                            </div>
                            <svg id="arrow-{{ $globalActividadIndex }}" class="w-5 h-5 text-gray-400 transition-transform transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    {{-- Tabla de Avances (Collapsible Content) --}}
                    <div id="content-{{ $globalActividadIndex }}" class="hidden">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 text-gray-400 uppercase font-black border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3">Mes</th>
                                    <th class="px-6 py-3 text-center">Programado</th>
                                    <th class="px-6 py-3 text-center">Realizado</th>
                                    <th class="px-6 py-3 text-center">Estado</th>
                                    <th class="px-6 py-3 text-center">Evidencias</th>
                                    <th class="px-6 py-3 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($actividad->programaciones as $prog)
                                    @php
                                        $meses = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
                                        
                                        // Determinar estado según las nuevas reglas
                                        if ($prog->cantidad_programada == 0) {
                                            $estado = 'NO APLICA';
                                            $badgeClass = 'bg-gray-50 text-gray-400 border-gray-200';
                                        } elseif ($prog->cantidad_ejecutada > $prog->cantidad_programada) {
                                            $estado = 'No Planificada';
                                            $badgeClass = 'bg-orange-100 text-orange-700 border-orange-200';
                                        } elseif ($prog->cantidad_ejecutada >= $prog->cantidad_programada) {
                                            $estado = 'CUMPLIDO';
                                            $badgeClass = 'bg-gray-900 text-white border-gray-900';
                                        } elseif ($prog->cantidad_ejecutada > 0) {
                                            $estado = 'PARCIAL';
                                            $badgeClass = 'bg-gray-200 text-gray-800 border-gray-300';
                                        } else {
                                            $estado = 'PENDIENTE';
                                            $badgeClass = 'bg-gray-100 text-gray-400 border-gray-200';
                                        }
                                        
                                        $evidenciasCount = $actividad->evidencias->where('mes', $prog->mes)->count();
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-gray-700">{{ $meses[$prog->mes] }}</td>
                                        <td class="px-6 py-4 text-center font-black text-gray-400">{{ $prog->cantidad_programada + 0 }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <form action="{{ route('poa.avances.update') }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="programacion_id" value="{{ $prog->id }}">
                                                <input type="number" name="cantidad_ejecutada" value="{{ $prog->cantidad_ejecutada + 0 }}" 
                                                       class="w-20 text-center border-gray-300 rounded text-xs font-bold focus:ring-black focus:border-black shadow-sm"
                                                       min="0" step="1">
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black border {{ $badgeClass }}">
                                                {{ $estado }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button onclick="openEvidenciaModal({{ $actividad->id }}, {{ $prog->mes }}, '{{ $meses[$prog->mes] }}')" 
                                                    class="flex items-center gap-1 mx-auto text-gray-400 hover:text-black transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                <span class="text-[10px] font-black">{{ $evidenciasCount }}</span>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button onclick="this.closest('tr').querySelector('form').submit()" 
                                                    class="bg-gray-900 text-white px-3 py-1 rounded text-[10px] font-black hover:bg-black transition-colors uppercase tracking-widest">
                                                Guardar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @php $globalActividadIndex++; @endphp
            @endforeach
        @endforeach
    </div>
</div>

{{-- MODAL DE EVIDENCIAS --}}
<dialog id="modal_evidencia" class="modal">
    <div class="modal-box w-11/12 max-w-3xl bg-white rounded-2xl p-0 overflow-hidden shadow-2xl">
        {{-- Header Modal --}}
        <div class="bg-gray-900 px-8 py-6 flex justify-between items-center">
            <div>
                <h3 class="text-white font-black text-lg uppercase tracking-widest">Gestionar Evidencias</h3>
                <p class="text-gray-400 text-xs font-bold mt-1">Mes: <span id="modal_mes_nombre" class="text-white"></span></p>
            </div>
            <form method="dialog">
                <button class="text-gray-400 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </form>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Formulario --}}
            <div>
                <h4 class="text-gray-900 font-black text-xs uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">Subir Nueva</h4>
                <form action="{{ route('poa.avances.store_evidencia') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="actividad_id" id="modal_actividad_id">
                    <input type="hidden" name="mes" id="modal_mes_input">

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Tipo de Evidencia</label>
                        <select name="tipo" class="w-full border-gray-200 rounded-lg text-xs font-bold focus:ring-black focus:border-black" required>
                            <option value="PDF">PDF</option>
                            <option value="FOTO">Foto</option>
                            <option value="VIDEO">Video</option>
                            <option value="URL">URL</option>
                            <option value="MP3">Audio MP3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Archivo (Opcional)</label>
                        <input type="file" name="archivo" class="w-full text-xs font-bold file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">URL (Opcional)</label>
                        <input type="url" name="url" class="w-full border-gray-200 rounded-lg text-xs font-bold focus:ring-black focus:border-black" placeholder="https://...">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Descripción</label>
                        <textarea name="descripcion" rows="2" class="w-full border-gray-200 rounded-lg text-xs font-bold focus:ring-black focus:border-black" placeholder="Breve descripción..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-gray-900 text-white font-black py-3 rounded-lg text-xs uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-lg">
                        Subir Evidencia
                    </button>
                </form>
            </div>

            {{-- Listado --}}
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                <h4 class="text-gray-900 font-black text-xs uppercase tracking-widest mb-4 border-b border-gray-200 pb-2">Evidencias Existentes</h4>
                <div id="evidencias_list" class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                    {{-- Se llena vía JS --}}
                    <div class="text-center py-10 text-gray-400 italic text-xs">Cargando...</div>
                </div>
            </div>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-black/60 backdrop-blur-sm">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
<script>
    function toggleActividad(index) {
        const content = document.getElementById(`content-${index}`);
        const arrow = document.getElementById(`arrow-${index}`);
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }

    function openEvidenciaModal(actividadId, mes, mesNombre) {
        const modal = document.getElementById('modal_evidencia');
        document.getElementById('modal_actividad_id').value = actividadId;
        document.getElementById('modal_mes_input').value = mes;
        document.getElementById('modal_mes_nombre').textContent = mesNombre;
        
        const listContainer = document.getElementById('evidencias_list');
        listContainer.innerHTML = '<div class="text-center py-10 text-gray-400 italic text-xs">Cargando...</div>';
        
        modal.showModal();

        fetch(`/poa/avances/evidencias-mes/${actividadId}/${mes}`)
            .then(res => res.json())
            .then(data => {
                if (data.evidencias.length === 0) {
                    listContainer.innerHTML = '<div class="text-center py-10 text-gray-400 italic text-xs">No hay evidencias registradas.</div>';
                    return;
                }
                
                listContainer.innerHTML = data.evidencias.map(ev => `
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-500 font-black text-[8px] uppercase">
                                ${ev.tipo}
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-800 line-clamp-1">${ev.descripcion || 'Sin descripción'}</p>
                                <p class="text-[8px] text-gray-400 font-bold uppercase">${ev.fecha_subida}</p>
                            </div>
                        </div>
                        <a href="${ev.url || ev.archivo}" target="_blank" class="text-gray-400 hover:text-black transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    </div>
                `).join('');
            });
    }
</script>
@endpush
@endsection
