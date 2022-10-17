<?php

namespace App\Http\Middleware;

use Closure;

class CORS
{
    public function handle($request, Closure $next)
    {
        $origin = $request->header('origin');
        if (empty($origin)) {
            $referer = $request->header('referer');
            if (!empty($referer) && preg_match("/^((https|http):\/\/)?([^\/]+)/i", $referer, $matches)) {
                $origin = $matches[0];
            }
        }
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', trim($origin, '/'));
        $response->header('Access-Control-Allow-Methods', 'GET,POST,OPTIONS,HEAD');
        $response->header('Access-Control-Allow-Headers', 'Origin,Content-Type,Accept,Authorization,X-Request-With');
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('Access-Control-Max-Age', 10080);

        return $response;
    }
}
