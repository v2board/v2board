<?php

namespace App\Http\Middleware;

use App\Utils\CacheKey;
use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Client
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
        $token = $request->input('token');
        if (empty($token) || $this->tokenNotInCache($token)) {
            abort(403, 'token is null');
        }
        $user = User::where('token', $token)->first();
        if (!$user) {
            abort(403, 'token is error');
        }
        $request->user = $user;
        return $next($request);
    }

    private function tokenNotInCache($token)
    {
        // schedule init complete?
        if (!Cache::get(CacheKey::get('SCHEDULE_LAST_CHECK_AT', null))) return true;
        if (Cache::get(CacheKey::get('SUBSCRIBE_TOKEN', $token))) return false;
        return true;
    }
}
