<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Redirect;

class ForcePasswordChangeController extends Controller
{
    /**
     * Muestra el formulario de cambio de contraseña obligatorio.
     */
    public function edit()
    {
        return view('auth.force-password-change');
    }

    /**
     * Procesa el cambio de contraseña.
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'], // Valida que la actual sea correcta
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()], // Nueva con confirmación
        ]);

        $user = $request->user();

        // Actualizar contraseña y quitar la obligatoriedad
        $user->update([
            'password' => Hash::make($request->password),
            'debe_cambiar_clave' => false,
        ]);

        // Redirigir según rol (Separación total)
        if ($user->role === 'admin') {
            return Redirect::to('/admin/dashboard')->with('status', '¡Contraseña actualizada correctamente!');
        }

        return Redirect::to('/dashboard')->with('status', '¡Bienvenido! Su contraseña ha sido actualizada.');
    }
}
