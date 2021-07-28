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
            $router->get ('/config/getThemeTemplate', 'Admin\\ConfigController@getThemeTemplate');
            $router->post('/config/setTelegramWebhook', 'Admin\\ConfigController@setTelegramWebhook');
            // Plan
            $router->get ('/plan/fetch', 'Admin\\PlanController@fetch');
            $router->post('/plan/save', 'Admin\\PlanController@save');
            $router->post('/plan/drop', 'Admin\\PlanController@drop');
            $router->post('/plan/update', 'Admin\\PlanController@update');
            $router->post('/plan/sort', 'Admin\\PlanController@sort');
            // Server
            $router->get ('/server/group/fetch', 'Admin\\Server\\GroupController@fetch');
            $router->post('/server/group/save', 'Admin\\Server\\GroupController@save');
            $router->post('/server/group/drop', 'Admin\\Server\\GroupController@drop');
            $router->get ('/server/manage/getNodes', 'Admin\\Server\\ManageController@getNodes');
            $router->post('/server/manage/sort', 'Admin\\Server\\ManageController@sort');
            $router->group([
                'prefix' => 'server/trojan'
            ], function ($router) {
                $router->get ('fetch', 'Admin\\Server\\TrojanController@fetch');
                $router->post('save', 'Admin\\Server\\TrojanController@save');
                $router->post('drop', 'Admin\\Server\\TrojanController@drop');
                $router->post('update', 'Admin\\Server\\TrojanController@update');
                $router->post('copy', 'Admin\\Server\\TrojanController@copy');
                $router->post('sort', 'Admin\\Server\\TrojanController@sort');
                $router->post('viewConfig', 'Admin\\Server\\TrojanController@viewConfig');
            });
            $router->group([
                'prefix' => 'server/v2ray'
            ], function ($router) {
                $router->get ('fetch', 'Admin\\Server\\V2rayController@fetch');
                $router->post('save', 'Admin\\Server\\V2rayController@save');
                $router->post('drop', 'Admin\\Server\\V2rayController@drop');
                $router->post('update', 'Admin\\Server\\V2rayController@update');
                $router->post('copy', 'Admin\\Server\\V2rayController@copy');
                $router->post('sort', 'Admin\\Server\\V2rayController@sort');
                $router->post('viewConfig', 'Admin\\Server\\V2rayController@viewConfig');
            });
            $router->group([
                'prefix' => 'server/shadowsocks'
            ], function ($router) {
                $router->get ('fetch', 'Admin\\Server\\ShadowsocksController@fetch');
                $router->post('save', 'Admin\\Server\\ShadowsocksController@save');
                $router->post('drop', 'Admin\\Server\\ShadowsocksController@drop');
                $router->post('update', 'Admin\\Server\\ShadowsocksController@update');
                $router->post('copy', 'Admin\\Server\\ShadowsocksController@copy');
                $router->post('sort', 'Admin\\Server\\ShadowsocksController@sort');
            });
            // Order
            $router->get ('/order/fetch', 'Admin\\OrderController@fetch');
            $router->post('/order/repair', 'Admin\\OrderController@repair');
            $router->post('/order/update', 'Admin\\OrderController@update');
            $router->post('/order/assign', 'Admin\\OrderController@assign');
            // User
            $router->get ('/user/fetch', 'Admin\\UserController@fetch');
            $router->post('/user/update', 'Admin\\UserController@update');
            $router->get ('/user/getUserInfoById', 'Admin\\UserController@getUserInfoById');
            $router->post('/user/generate', 'Admin\\UserController@generate');
            $router->post('/user/dumpCSV', 'Admin\\UserController@dumpCSV');
            $router->post('/user/sendMail', 'Admin\\UserController@sendMail');
            $router->post('/user/ban', 'Admin\\UserController@ban');
            $router->post('/user/resetSecret', 'Admin\\UserController@resetSecret');
            $router->post('/user/setInviteUser', 'Admin\\UserController@setInviteUser');
            // StatOrder
            $router->get ('/stat/getOverride', 'Admin\\StatController@getOverride');
            $router->get ('/stat/getServerLastRank', 'Admin\\StatController@getServerLastRank');
            $router->get ('/stat/getOrder', 'Admin\\StatController@getOrder');
            // Notice
            $router->get ('/notice/fetch', 'Admin\\NoticeController@fetch');
            $router->post('/notice/save', 'Admin\\NoticeController@save');
            $router->post('/notice/update', 'Admin\\NoticeController@update');
            $router->post('/notice/drop', 'Admin\\NoticeController@drop');
            // Ticket
            $router->get ('/ticket/fetch', 'Admin\\TicketController@fetch');
            $router->post('/ticket/reply', 'Admin\\TicketController@reply');
            $router->post('/ticket/close', 'Admin\\TicketController@close');
            // Coupon
            $router->get ('/coupon/fetch', 'Admin\\CouponController@fetch');
            $router->post('/coupon/generate', 'Admin\\CouponController@generate');
            $router->post('/coupon/drop', 'Admin\\CouponController@drop');
            // Knowledge
            $router->get ('/knowledge/fetch', 'Admin\\KnowledgeController@fetch');
            $router->get ('/knowledge/getCategory', 'Admin\\KnowledgeController@getCategory');
            $router->post('/knowledge/save', 'Admin\\KnowledgeController@save');
            $router->post('/knowledge/show', 'Admin\\KnowledgeController@show');
            $router->post('/knowledge/drop', 'Admin\\KnowledgeController@drop');
            $router->post('/knowledge/sort', 'Admin\\KnowledgeController@sort');
            // Payment
            $router->get ('/payment/fetch', 'Admin\\PaymentController@fetch');
            $router->get ('/payment/getPaymentMethods', 'Admin\\PaymentController@getPaymentMethods');
            $router->post('/payment/getPaymentForm', 'Admin\\PaymentController@getPaymentForm');
            $router->post('/payment/save', 'Admin\\PaymentController@save');
            $router->post('/payment/drop', 'Admin\\PaymentController@drop');
        });
    }
}
