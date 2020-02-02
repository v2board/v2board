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
            Route::get('/subscribe', 'Client\\ClientController@subscribe');
            // App
            Route::get('/app/data', 'Client\\AppController@data');
            Route::get('/app/config', 'Client\\AppController@config');
        });
    }
}
