{{-- resources/views/components/modals/confirm-delete.blade.php --}}
@props([
    'id' => 'delete_modal',
    'formId' => 'delete_form',
    'title' => '¿Eliminar registro?',
    'message' => 'Esta acción es permanente y no se puede deshacer.'
])

<dialog id="{{ $id }}" class="modal">
    <div class="modal-box bg-white rounded-lg p-0 shadow-2xl max-w-sm">
        {{-- Icono y Header --}}
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">{{ $title }}</h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500">{{ $message }}</p>
            </div>
        </div>

        {{-- Footer de Acciones --}}
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
            <form id="{{ $formId }}" method="POST" action="" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')

                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                    Confirmar Eliminación
                </button>
            </form>

            <form method="dialog" class="w-full sm:w-auto mt-3 sm:mt-0">
                <button class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-congress-blue-500 sm:text-sm">
                    Cancelar
                </button>
            </form>
        </div>
    </div>

    {{-- Backdrop para cerrar al hacer clic fuera --}}
    <form method="dialog" class="modal-backdrop bg-gray-900/20">
        <button>close</button>
    </form>
</dialog>
