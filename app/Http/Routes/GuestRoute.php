<?php
namespace App\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

class GuestRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'guest'
        ], function ($router) {
            // Plan
            $router->get ('/plan/fetch', 'Guest\\PlanController@fetch');
            // Telegram
            $router->post('/telegram/webhook', 'Guest\\TelegramController@webhook');
            // Payment
            $router->match(['get', 'post'], '/payment/notify/{method}/{uuid}', 'Guest\\PaymentController@notify');
            // Comm
            $router->get ('/comm/config', 'Guest\\CommController@config');
        });
    }
}
