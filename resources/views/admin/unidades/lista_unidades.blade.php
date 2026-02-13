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

    {{-- Contenedor de resultados (AJAX) --}}
    <div id="resultados-busqueda">
        @include('admin.unidades.partials.list')
    </div>
</div>

<script>
    // Buscador dinámico de unidades
    const buscador = document.getElementById('buscador-unidades');
    let timeoutId;
    
    // Función para realizar la búsqueda vía AJAX
    const realizarBusqueda = (url) => {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Reemplazar contenido del contenedor de resultados
            const contenedor = document.getElementById('resultados-busqueda');
            if (contenedor) {
                contenedor.innerHTML = html;
            }
        })
        .catch(error => console.error('Error al buscar:', error));
    };

    buscador.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const busqueda = this.value;
        
        timeoutId = setTimeout(() => {
            // Construir URL
            const url = new URL(window.location);
            if (busqueda) {
                url.searchParams.set('buscar', busqueda);
            } else {
                url.searchParams.delete('buscar');
            }
            
            // Reset pagination
            url.searchParams.delete('page');
            
            // Actualizar URL en el navegador sin recargar
            window.history.pushState({}, '', url);
            
            // Ejecutar búsqueda AJAX
            realizarBusqueda(url);
        }, 300); // 300ms de debounce (más rápido)
    });

    // Manejar botones de paginación para que también sean AJAX si se desea (opcional, por ahora solo arreglamos el buscador)
    // Pero si el usuario cambia de página y luego busca, ya manejamos el reset de página.
</script>
@endsection
