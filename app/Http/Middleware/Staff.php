<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

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
        if (!$authorization) abort(403, 'Not logged in or login has expired');

        $authData = explode(':', base64_decode($authorization));
        if (!Cache::has($authorization)) {
            if (!isset($authData[1]) || !isset($authData[0])) abort(403, 'Authentication failed, please login again');
            $user = \App\Models\User::where('password', $authData[1])
                ->where('email', $authData[0])
                ->select([
                    'id',
                    'email',
                    'is_admin',
                    'is_staff'
                ])
                ->first();
            if (!$user) abort(403, 'Authentication failed, please login again');
            if (!$user->is_staff) abort(403, 'Authentication failed, please login again');
            Cache::put($authorization, $user->toArray(), 3600);
        }
        $request->merge([
            'user' => Cache::get($authorization)
        ]);
        return $next($request);
    }
}
