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
            $router->get ('/info', 'User\\UserController@info');
            $router->post('/changePassword', 'User\\UserController@changePassword');
            $router->post('/update', 'User\\UserController@update');
            $router->get ('/getSubscribe', 'User\\UserController@getSubscribe');
            $router->get ('/getStat', 'User\\UserController@getStat');
            $router->get ('/checkLogin', 'User\\UserController@checkLogin');
            $router->post('/transfer', 'User\\UserController@transfer');
            $router->post('/getQuickLoginUrl', 'User\\UserController@getQuickLoginUrl');
            $router->get ('/getActiveSession', 'User\\UserController@getActiveSession');
            $router->post('/removeActiveSession', 'User\\UserController@removeActiveSession');
            // Order
            $router->post('/order/save', 'User\\OrderController@save');
            $router->post('/order/checkout', 'User\\OrderController@checkout');
            $router->get ('/order/check', 'User\\OrderController@check');
            $router->get ('/order/detail', 'User\\OrderController@detail');
            $router->get ('/order/fetch', 'User\\OrderController@fetch');
            $router->get ('/order/getPaymentMethod', 'User\\OrderController@getPaymentMethod');
            $router->post('/order/cancel', 'User\\OrderController@cancel');
            // Plan
            $router->get ('/plan/fetch', 'User\\PlanController@fetch');
            // Invite
            $router->get ('/invite/save', 'User\\InviteController@save');
            $router->get ('/invite/fetch', 'User\\InviteController@fetch');
            $router->get ('/invite/details', 'User\\InviteController@details');
            // Notice
            $router->get ('/notice/fetch', 'User\\NoticeController@fetch');
            // Ticket
            $router->post('/ticket/reply', 'User\\TicketController@reply');
            $router->post('/ticket/close', 'User\\TicketController@close');
            $router->post('/ticket/save', 'User\\TicketController@save');
            $router->get ('/ticket/fetch', 'User\\TicketController@fetch');
            $router->post('/ticket/withdraw', 'User\\TicketController@withdraw');
            // Server
            $router->get ('/server/fetch', 'User\\ServerController@fetch');
            // Coupon
            $router->post('/coupon/check', 'User\\CouponController@check');
            // Telegram
            $router->get ('/telegram/getBotInfo', 'User\\TelegramController@getBotInfo');
            // Comm
            $router->get ('/comm/config', 'User\\CommController@config');
            $router->Post('/comm/getStripePublicKey', 'User\\CommController@getStripePublicKey');
            // Knowledge
            $router->get ('/knowledge/fetch', 'User\\KnowledgeController@fetch');
            $router->get ('/knowledge/getCategory', 'User\\KnowledgeController@getCategory');
            // Stat
            $router->get ('/stat/getTrafficLog', 'User\\StatController@getTrafficLog');
        });
    }
}
