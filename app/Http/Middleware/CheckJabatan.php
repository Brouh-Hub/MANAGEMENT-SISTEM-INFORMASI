<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckJabatan
{
    public function handle(Request $request, Closure $next, string ...$jabatans)
    {
        if (!auth()->check() || !in_array(auth()->user()->jabatan, $jabatans)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
