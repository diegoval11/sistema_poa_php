@props(['id' => 'action_modal'])

<dialog id="{{ $id }}" class="modal">
    {{-- ESTRUCTURA IDÉNTICA A STEP 2/3 (max-w-sm, rounded-lg, shadow-2xl) --}}
    <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">

        {{-- CUERPO CENTRAL --}}
        <div class="p-6 text-center">
            {{-- Contenedor del Icono Dinámico (Círculo) --}}
            <div id="modal_icon_wrapper" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 transition-colors duration-200">
                {{-- El SVG se inyectará aquí --}}
            </div>

            {{-- Títulos --}}
            <h3 id="modal_title" class="text-lg leading-6 font-bold text-gray-900"></h3>
            <div class="mt-2">
                <p id="modal_desc" class="text-sm text-gray-500"></p>
            </div>
        </div>

        {{-- FOOTER GRIS (Igual a Step 2/3) --}}
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
            {{-- Formulario de Acción --}}
            <form id="action_form" method="POST" action="" class="w-full sm:w-auto">
                @csrf
                <button id="modal_confirm_btn" type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm transition-colors">
                    {{-- Texto botón --}}
                </button>
            </form>

            {{-- Botón Cancelar --}}
            <form method="dialog" class="w-full sm:w-auto mt-3 sm:mt-0">
                <button class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-congress-blue-500 sm:text-sm">
                    Cancelar
                </button>
            </form>
        </div>
    </div>

    {{-- Backdrop --}}
    <form method="dialog" class="modal-backdrop bg-gray-900/20"><button>close</button></form>
</dialog>

@push('scripts')
<script>
    function openModal(url, type) {
        const modal = document.getElementById('{{ $id }}');
        const form = document.getElementById('action_form');

        // Elementos UI
        const iconWrapper = document.getElementById('modal_icon_wrapper');
        const title = document.getElementById('modal_title');
        const desc = document.getElementById('modal_desc');
        const btn = document.getElementById('modal_confirm_btn');

        if(!modal) return console.error('Modal no encontrado');

        form.action = url;

        if (type === 'delete') {
            // === ESTILO ROJO (Igual a Borrar Meta) ===
            // Icono: Exclamación
            iconWrapper.className = "mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4";
            iconWrapper.innerHTML = '<svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';

            title.innerText = "¿Eliminar Proyecto?";
            desc.innerText = "Esta acción eliminará permanentemente el borrador y todas sus metas. No se puede deshacer.";

            // Botón Rojo
            btn.className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm";
            btn.innerText = "Confirmar Eliminación";
        }
        else if (type === 'send') {
            // === ESTILO AZUL (Nuevo para Enviar) ===
            // Icono: Avión de papel
            iconWrapper.className = "mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4";
            iconWrapper.innerHTML = '<svg class="h-6 w-6 text-blue-600 transform -rotate-45 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>';

            title.innerText = "¿Enviar a Revisión?";
            desc.innerText = "El proyecto se bloqueará para edición y será enviado al departamento de planificación.";

            // Botón Azul
            btn.className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm";
            btn.innerText = "Enviar Proyecto";
        }

        modal.showModal();
    }
</script>
@endpush
