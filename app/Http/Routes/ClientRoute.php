<?php
namespace App\Http\Routes;

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
            $router->get('/subscribe', 'Client\\ClientController@subscribe');
            // App
            $router->get('/app/config', 'Client\\AppController@config');
            $router->get('/app/getConfig', 'Client\\AppController@getConfig');
            $router->get('/app/getVersion', 'Client\\AppController@getVersion');
        });
    }
}
