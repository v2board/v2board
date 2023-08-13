<?php
namespace App\Http\Routes\V2;

use Illuminate\Contracts\Routing\Registrar;

class AdminRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => config('v2board.secure_path', config('v2board.frontend_admin_path', hash('crc32b', config('app.key')))),
            'middleware' => ['admin', 'log'],
        ], function ($router) {
            // Stat
            $router->get ('/stat/override', 'V2\\Admin\\StatController@override');
            $router->get ('/stat/record', 'V2\\Admin\\StatController@record');
            $router->get ('/stat/ranking', 'V2\\Admin\\StatController@ranking');
        });
    }
}
