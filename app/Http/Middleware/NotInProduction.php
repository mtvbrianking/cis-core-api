<?php

namespace App\Http\Middleware;

use Closure;

class NotInProduction
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('app.env') == 'production') {
            abort(403, "Don't run in production.");
        }

        return $next($request);
    }
}
