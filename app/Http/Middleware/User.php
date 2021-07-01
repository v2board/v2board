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
        if ($request->input('auth_data')) {
            $authData = explode(':', base64_decode($request->input('auth_data')));
            if (!isset($authData[1]) || !isset($authData[0])) abort(403, '鉴权失败，请重新登入');
            $user = \App\Models\User::where('password', $authData[1])
                ->where('email', $authData[0])
                ->first();
            if (!$user) abort(403, '鉴权失败，请重新登入');
            $request->session()->put('email', $user->email);
            $request->session()->put('id', $user->id);
        }
        if (!$request->session()->get('id')) {
            abort(403, '未登录或登陆已过期');
        }
        return $next($request);
    }
}
