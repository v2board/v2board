<?php

namespace App\Http\Middleware;

use Closure;

class Staff
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->session()->get('is_staff')) {
            abort(403, '权限不足');
        }
        return $next($request);
    }
}
