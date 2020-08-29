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
            // Order
            $router->post('/order/alipayNotify', 'Guest\\OrderController@alipayNotify');
            $router->post('/order/stripeNotify', 'Guest\\OrderController@stripeNotify');
            $router->post('/order/bitpayXNotify', 'Guest\\OrderController@bitpayXNotify');
            $router->post('/order/mgateNotify', 'Guest\\OrderController@mgateNotify');
            $router->post('/order/epayNotify', 'Guset\\OrderController@epayNotify');
            // Telegram
            $router->post('/telegram/webhook', 'Guest\\TelegramController@webhook');
        });
    }
}
