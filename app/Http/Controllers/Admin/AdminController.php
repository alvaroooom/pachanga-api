<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Base de los controladores del panel de administracion.
 */
abstract class AdminController extends Controller
{
    /** Elementos por pagina (?per_page=), por defecto 15 y maximo 100. */
    protected function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
