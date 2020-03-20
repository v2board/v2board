<?php

namespace App\Http\Middleware;

use Closure;

class CORS
{
    public function handle($request, Closure $next)
    {
        $domains = explode(',', config('cors.domain'));
        if(!config('app.debug')){ #remove localhost when not debug
            $domains = array_filter($domains,function ($domain){
                if(!strpos($domain,"localhost")) {
                    return ($domain);
                }
            });
        }

        $origin = $request->header('origin');
        if (in_array($origin, $domains)) {
            header('Access-Control-Allow-Origin:'. $origin);
            header('Access-Control-Allow-Methods:'. 'GET,POST,OPTIONS');
            header('Access-Control-Allow-Headers:'. 'Content-Type,X-Requested-With');
            header('Access-Control-Allow-Credentials:'. 'true');
            header('Access-Control-Max-Age:'. 10080);
        }
        return $next($request);
    }
}
