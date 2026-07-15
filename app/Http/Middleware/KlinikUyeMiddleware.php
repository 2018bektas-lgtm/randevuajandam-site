<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KlinikUyeMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('doktor')->user();

        if (! $user || ! $user->klinikteMi()) {
            abort(403, 'Bu sayfaya erişmek için bir kliniğe bağlı olmanız gerekmektedir.');
        }

        return $next($request);
    }
}
