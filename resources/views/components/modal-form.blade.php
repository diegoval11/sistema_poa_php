@props(['id', 'title', 'maxWidth' => '2xl'])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        default => 'max-w-2xl'
    };
@endphp

<dialog id="{{ $id }}" class="modal">
    <div class="modal-box {{ $maxWidthClass }} bg-white rounded-2xl p-0 shadow-2xl border border-gray-100">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-congress-blue-600 to-congress-blue-700 px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">{{ $title }}</h3>
        </div>

        {{-- Formulario completo --}}
        {{ $slot }}
    </div>
    <form method="dialog" class="modal-backdrop bg-gray-900/30"><button>close</button></form>
</dialog>
