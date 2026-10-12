<?php

namespace App\Http\Middleware;

use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        $middleware->alias([
            'role'    => CheckRole::class,
            'jabatan' => CheckJabatan::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

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
