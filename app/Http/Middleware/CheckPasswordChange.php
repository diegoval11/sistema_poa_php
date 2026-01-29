<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está logueado y tiene la marca de "debe cambiar clave" activada...
        if ($request->user() && $request->user()->debe_cambiar_clave) {

            // ... y NO está intentando entrar a las rutas de cambio de clave (para evitar bucles infinitos)
            if (!$request->routeIs('password.change.*')) {
                return redirect()->route('password.change.notice');
            }
        }

        return $next($request);
    }
}
