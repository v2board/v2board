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
        $authorization = $request->input('auth_data') ?? $request->header('authorization');
        if (!$authorization) abort(403, '未登录或登陆已过期');

        $authData = explode(':', base64_decode($authorization));
        if (!isset($authData[1]) || !isset($authData[0])) abort(403, '鉴权失败，请重新登入');
        $user = \App\Models\User::where('password', $authData[1])
            ->where('email', $authData[0])
            ->first();
        if (!$user) abort(403, '鉴权失败，请重新登入');
        if (!$user->is_staff) abort(403, '未登录或登陆已过期');
        $request->merge([
            'user' => $user
        ]);
        return $next($request);
    }
}
