<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     * Hanya izinkan user dengan role super_admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Full Admin yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
