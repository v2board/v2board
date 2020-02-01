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
            $router->get('resetSecurity', 'UserController@resetSecurity');
            $router->get('logout', 'UserController@logout');
            $router->get('info', 'UserController@info');
            $router->post('changePassword', 'UserController@changePassword');
            $router->post('update', 'UserController@update');
            $router->get('getSubscribe', 'UserController@getSubscribe');
            $router->get('getStat', 'UserController@getStat');
            // Order
            $router->post('order/save', 'OrderController@save');
            $router->post('order/checkout', 'OrderController@checkout');
            $router->get('order/check', 'OrderController@check');
            $router->get('order/details', 'OrderController@details');
            $router->get('order/fetch', 'OrderController@fetch');
            $router->get('order/getPaymentMethod', 'OrderController@getPaymentMethod');
            $router->post('order/cancel', 'OrderController@cancel');
            // Plan
            $router->get('plan/fetch', 'PlanController@fetch');
            // Invite
            $router->get('invite/save', 'InviteController@save');
            $router->get('invite/fetch', 'InviteController@fetch');
            $router->get('invite/details', 'InviteController@details');
            // Tutorial
            $router->get('tutorial/getSubscribeUrl', 'TutorialController@getSubscribeUrl');
            $router->get('tutorial/getAppleID', 'TutorialController@getAppleID');
            $router->get('tutorial/fetch', 'TutorialController@fetch');
            // Notice
            $router->get('notice/fetch', 'NoticeController@fetch');
            // Ticket
            $router->post('ticket/reply', 'TicketController@reply');
            $router->post('ticket/close', 'TicketController@close');
            $router->post('ticket/save', 'TicketController@save');
            $router->get('ticket/fetch', 'TicketController@fetch');
            // Server
            $router->get('server/fetch', 'ServerController@fetch');
            $router->get('server/log/fetch', 'ServerController@logFetch');
            // Coupon
            $router->post('coupon/check', 'CouponController@check');
        });
    }
}
