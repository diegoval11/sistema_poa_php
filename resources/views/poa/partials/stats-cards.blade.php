{{-- resources/views/poa/partials/stats-cards.blade.php --}}
@props(['proyectos', 'cumplimientoGeneral' => 0])

{{-- Card Cumplimiento General --}}
<div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col justify-between h-32 relative overflow-hidden group hover:border-congress-blue-200 transition-colors">
    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
        <svg class="w-20 h-20 text-congress-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
    </div>
    <div class="text-sm font-bold text-gray-400 uppercase tracking-wide">Cumplimiento General</div>
    <div class="flex items-end gap-2">
        <div class="text-4xl font-black {{ $cumplimientoGeneral >= 80 ? 'text-green-600' : ($cumplimientoGeneral >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
            {{ $cumplimientoGeneral }}%
        </div>
    </div>
</div>

{{-- Card Total --}}
<div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col justify-between h-32 relative overflow-hidden group hover:border-congress-blue-200 transition-colors">
    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
        <svg class="w-20 h-20 text-congress-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/></svg>
    </div>
    <div class="text-sm font-bold text-gray-400 uppercase tracking-wide">Total Proyectos</div>
    <div class="text-4xl font-black text-congress-blue-800">{{ $proyectos->count() }}</div>
</div>

{{-- Card Aprobados --}}
<div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col justify-between h-32 relative overflow-hidden">
    <div class="text-sm font-bold text-gray-400 uppercase tracking-wide">En Ejecución</div>
    <div class="flex items-end gap-2">
        <span class="text-4xl font-black text-gray-800">{{ $proyectos->where('estado', 'APROBADO')->count() }}</span>
        <span class="mb-1 text-xs font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Aprobados</span>
    </div>
</div>

{{-- Card Revisión --}}
<div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col justify-between h-32 relative overflow-hidden">
    <div class="text-sm font-bold text-gray-400 uppercase tracking-wide">En Revisión</div>
    <div class="flex items-end gap-2">
        <span class="text-4xl font-black text-gray-800">{{ $proyectos->where('estado', 'ENVIADO')->count() }}</span>
        <span class="mb-1 text-xs font-bold bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Pendientes</span>
    </div>
</div>

{{-- Card Borradores --}}
<div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col justify-between h-32 relative overflow-hidden">
    <div class="text-sm font-bold text-gray-400 uppercase tracking-wide">Borradores</div>
    <div class="flex items-end gap-2">
        <span class="text-4xl font-black text-gray-800">{{ $proyectos->where('estado', 'BORRADOR')->count() }}</span>
        <span class="mb-1 text-xs font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Editando</span>
    </div>
</div>
