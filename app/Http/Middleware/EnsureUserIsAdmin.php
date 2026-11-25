<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Solo deja pasar a los usuarios con rol 'admin'.
 * Se usa como 'admin' en las rutas del panel de administracion.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            return response()->json(['message' => 'Acceso solo para administradores.'], 403);
        }

        return $next($request);
    }
}
