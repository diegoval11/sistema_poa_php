<x-guest-layout>
    <div class="w-full max-w-md mx-auto bg-white shadow-xl rounded-2xl overflow-hidden p-8">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-black text-red-600">Cambio de Contraseña Requerido</h2>
            <p class="text-gray-600 text-sm mt-2">
                Por seguridad institucional, debe actualizar su contraseña antes de continuar.
            </p>
        </div>

        <form method="POST" action="{{ route('password.change.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                <input type="password" name="current_password" class="block w-full px-4 py-2 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500" required autofocus>
                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                <input type="password" name="password" class="block w-full px-4 py-2 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500" required>
                <p class="text-xs text-gray-400 mt-1">Mínimo 8 caracteres, números y letras.</p>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" class="block w-full px-4 py-2 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500" required>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg shadow transition duration-300">
                Actualizar y Continuar
            </button>
        </form>
    </div>
</x-guest-layout>
