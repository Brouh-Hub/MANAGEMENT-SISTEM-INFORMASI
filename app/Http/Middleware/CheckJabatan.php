<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckJabatan
{
    public function handle(Request $request, Closure $next, string ...$jabatan): Response
    {
        $jabatanUser = strtolower(trim($request->user()?->jabatan ?? ''));
        $diizinkan   = array_map(fn ($j) => strtolower(trim($j)), $jabatan);

        if (! in_array($jabatanUser, $diizinkan, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
