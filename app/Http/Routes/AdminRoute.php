<?php
namespace App\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

class AdminRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'admin',
            'middleware' => 'admin'
        ], function ($router) {
            // Config
            $router->get ('/config/fetch', 'Admin\\ConfigController@fetch');
            $router->post('/config/save', 'Admin\\ConfigController@save');
            $router->get ('/config/getEmailTemplate', 'Admin\\ConfigController@getEmailTemplate');
            $router->post('/config/setTelegramWebhook', 'Admin\\ConfigController@setTelegramWebhook');
            // Plan
            $router->get ('/plan/fetch', 'Admin\\PlanController@fetch');
            $router->post('/plan/save', 'Admin\\PlanController@save');
            $router->post('/plan/drop', 'Admin\\PlanController@drop');
            $router->post('/plan/update', 'Admin\\PlanController@update');
            $router->post('/plan/sort', 'Admin\\PlanController@sort');
            // Server
            $router->get ('/server/fetch', 'Admin\\ServerController@fetch');
            $router->post('/server/save', 'Admin\\ServerController@save');
            $router->get ('/server/group/fetch', 'Admin\\ServerController@groupFetch');
            $router->post('/server/group/save', 'Admin\\ServerController@groupSave');
            $router->post('/server/group/drop', 'Admin\\ServerController@groupDrop');
            $router->post('/server/drop', 'Admin\\ServerController@drop');
            $router->post('/server/update', 'Admin\\ServerController@update');
            $router->post('/server/copy', 'Admin\\ServerController@copy');
            $router->post('/server/viewConfig', 'Admin\\ServerController@viewConfig');
            $router->post('/server/sort', 'Admin\\ServerController@sort');
            // Order
            $router->get ('/order/fetch', 'Admin\\OrderController@fetch');
            $router->post('/order/repair', 'Admin\\OrderController@repair');
            $router->post('/order/update', 'Admin\\OrderController@update');
            $router->post('/order/assign', 'Admin\\OrderController@assign');
            // User
            $router->get ('/user/fetch', 'Admin\\UserController@fetch');
            $router->post('/user/update', 'Admin\\UserController@update');
            $router->get ('/user/getUserInfoById', 'Admin\\UserController@getUserInfoById');
            // Stat
            $router->get ('/stat/getOverride', 'Admin\\StatController@getOverride');
            // Notice
            $router->get ('/notice/fetch', 'Admin\\NoticeController@fetch');
            $router->post('/notice/save', 'Admin\\NoticeController@save');
            $router->post('/notice/update', 'Admin\\NoticeController@update');
            $router->post('/notice/drop', 'Admin\\NoticeController@drop');
            // Ticket
            $router->get ('/ticket/fetch', 'Admin\\TicketController@fetch');
            $router->post('/ticket/reply', 'Admin\\TicketController@reply');
            $router->post('/ticket/close', 'Admin\\TicketController@close');
            // Mail
            $router->post('/mail/send', 'Admin\\MailController@send');
            // Coupon
            $router->get ('/coupon/fetch', 'Admin\\CouponController@fetch');
            $router->post('/coupon/save', 'Admin\\CouponController@save');
            $router->post('/coupon/drop', 'Admin\\CouponController@drop');
            // Tutorial
            $router->get ('/tutorial/fetch', 'Admin\\TutorialController@fetch');
            $router->post('/tutorial/save', 'Admin\\TutorialController@save');
            $router->post('/tutorial/show', 'Admin\\TutorialController@show');
            $router->post('/tutorial/drop', 'Admin\\TutorialController@drop');
            $router->post('/tutorial/sort', 'Admin\\TutorialController@sort');
        });
    }
}
