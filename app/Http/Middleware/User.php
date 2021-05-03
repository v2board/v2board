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
            $user = \App\Models\User::where('password', $authData[1])
                ->where('email', $authData[0])
                ->first();
            if ($user) {
                $request->session()->put('email', $user->email);
                $request->session()->put('id', $user->id);
            }
        }
//        if ($request->input('lang')) {
//            $request->session()->put('lang', $request->input('lang'));
//        }
//        if ($request->session()->get('lang')) {
//            App::setLocale($request->session()->get('lang'));
//        }
        if (!$request->session()->get('id')) {
            abort(403, '未登录或登陆已过期');
        }
        return $next($request);
    }
}
