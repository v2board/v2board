<?php
namespace App\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

class UserRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'user',
            'middleware' => 'user'
        ], function ($router) {
            // User
            $router->get ('/resetSecurity', 'User\\UserController@resetSecurity');
            $router->get ('/logout', 'User\\UserController@logout');
            $router->get ('/info', 'User\\UserController@info');
            $router->post('/changePassword', 'User\\UserController@changePassword');
            $router->post('/update', 'User\\UserController@update');
            $router->get ('/getSubscribe', 'User\\UserController@getSubscribe');
            $router->get ('/getStat', 'User\\UserController@getStat');
            // Order
            $router->post('/order/save', 'User\\OrderController@save');
            $router->post('/order/checkout', 'User\\OrderController@checkout');
            $router->get ('/order/check', 'User\\OrderController@check');
            $router->get ('/order/details', 'User\\OrderController@details');
            $router->get ('/order/fetch', 'User\\OrderController@fetch');
            $router->get ('/order/getPaymentMethod', 'User\\OrderController@getPaymentMethod');
            $router->post('/order/cancel', 'User\\OrderController@cancel');
            // TODO 二次开发
            $router->get ('/order/idtPay', 'User\\OrderController@idtPay');
            // Plan
            $router->get ('/plan/fetch', 'User\\PlanController@fetch');
            // Invite
            $router->get ('/invite/save', 'User\\InviteController@save');
            $router->get ('/invite/fetch', 'User\\InviteController@fetch');
            $router->get ('/invite/details', 'User\\InviteController@details');
            // Tutorial
            $router->get ('/tutorial/getSubscribeUrl', 'User\\TutorialController@getSubscribeUrl');
            $router->get ('/tutorial/getAppleID', 'User\\TutorialController@getAppleID');
            $router->get ('/tutorial/fetch', 'User\\TutorialController@fetch');
            // Notice
            $router->get ('/notice/fetch', 'User\\NoticeController@fetch');
            // Ticket
            $router->post('/ticket/reply', 'User\\TicketController@reply');
            $router->post('/ticket/close', 'User\\TicketController@close');
            $router->post('/ticket/save', 'User\\TicketController@save');
            $router->get ('/ticket/fetch', 'User\\TicketController@fetch');
            // Server
            $router->get ('/server/fetch', 'User\\ServerController@fetch');
            $router->get ('/server/log/fetch', 'User\\ServerController@logFetch');
            // Coupon
            $router->post('/coupon/check', 'User\\CouponController@check');
        });
    }
}
