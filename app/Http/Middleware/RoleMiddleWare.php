<?php

namespace App\Http\MiddleWare;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles  Daftar role yang diizinkan (mis. "admin" atau "admin,petugas")
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Pastikan user sudah login
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // 2. Jika tidak ada role yang dibatasi, izinkan lewat
        if (empty($roles)) {
            return $next($request);
        }

        // 3. Cek apakah role user termasuk yang diizinkan
        if (! in_array($request->user()->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}