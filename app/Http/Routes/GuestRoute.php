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
            $router->post('/order/payTaroNotify', 'Guest\\OrderController@payTaroNotify');
            // TODO 二次开发
            $router->get('/order/idtNotify', 'Guest\\OrderController@idtNotify');
        });
    }
}
