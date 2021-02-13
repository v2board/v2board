<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class Language
{
    public function handle($request, Closure $next)
    {
        if ($request->header('content-language')) {
            App::setLocale($request->header('content-language'));
        }
        return $next($request);
    }
}
