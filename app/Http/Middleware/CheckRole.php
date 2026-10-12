<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$args)
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $user = auth()->user();

        foreach ($args as $arg) {
            if (str_contains($arg, ':')) {
                [$field, $value] = explode(':', $arg, 2);
                if ($user->{$field} == $value) {
                    return $next($request);
                }
            } else {
                if ($user->role === $arg) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Unauthorized');
    }
}
