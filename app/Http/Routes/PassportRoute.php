<?php
namespace App\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

class PassportRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'passport'
        ], function ($router) {
            // TODO: 1.1.1 abolish
            $router->post('/login', 'Passport\\AuthController@login');
            // Auth
            $router->post('/auth/register', 'Passport\\AuthController@register');
            $router->post('/auth/login', 'Passport\\AuthController@login');
            $router->get ('/auth/token2Login', 'Passport\\AuthController@token2Login');
            $router->get ('/auth/check', 'Passport\\AuthController@check');
            $router->post('/auth/forget', 'Passport\\AuthController@forget');
            // Comm
            $router->get ('/comm/config', 'Passport\\CommController@config');
            $router->post('/comm/sendEmailVerify', 'Passport\\CommController@sendEmailVerify');
            $router->post('/comm/pv', 'Passport\\CommController@pv');
        });
    }
}
