<?php

namespace App\Http\Middleware;

use App\Traits\RedirectByRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Solo redirigir si es una petición GET (no POST)
                if ($request->method() === 'GET') {
                    return $this->redirectByRole();
                }
            }
        }

        return $next($request);
    }
}
