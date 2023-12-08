<?php
namespace App\Http\Routes\V1;

use Illuminate\Contracts\Routing\Registrar;

class ClientRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'client',
            'middleware' => 'client'
        ], function ($router) {
            // Client
            if (!config('v2board.subscribe_path')) {
                $router->get('/subscribe', 'V1\\Client\\ClientController@subscribe');
            }
            // App
            $router->get('/app/getConfig', 'V1\\Client\\AppController@getConfig');
            $router->get('/app/getVersion', 'V1\\Client\\AppController@getVersion');
        });
        if (config('v2board.subscribe_path')) {
            \Route::get(config('v2board.subscribe_path'), 'V1\\Client\\ClientController@subscribe');
        }
    }
}
