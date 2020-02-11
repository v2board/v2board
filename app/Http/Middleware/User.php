<?php

namespace App\Http\Middleware;

use Closure;

class User
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
        if ($request->input('access_token')) {
            $user = \App\Models\User::where('token', $request->input('access_token'))->first();
            if ($user) {
                $request->session()->put('email', $user->email);
                $request->session()->put('id', $user->id);
            }
        }
        if (!$request->session()->get('id')) {
            abort(403, '未登录或登陆已过期');
        }
        return $next($request);
    }
}
