<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{

public function handle(Request $request, Closure $next, string $role): Response
{
    // Si el usuario no está autenticado o no tiene el rol correcto
    if (!$request->user() || $request->user()->role !== $role) {
        
        // Redirigir al dashboard correspondiente según el rol del usuario actual
        $redirectRoute = match ($request->user()?->role) {
            'admin' => '/admin/dashboard',
            'unidad' => '/dashboard',
            default => '/login', // Si no tiene rol válido, al login
        };

        return redirect($redirectRoute)->with('error', 'No tienes permisos para acceder a esta seccion...');
    }

    return $next($request);
}
}
