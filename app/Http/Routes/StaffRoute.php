<?php
namespace App\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

class StaffRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'staff',
            'middleware' => 'staff'
        ], function ($router) {
            // Ticket
            $router->get ('/ticket/fetch', 'Staff\\TicketController@fetch');
            $router->post('/ticket/reply', 'Staff\\TicketController@reply');
            $router->post('/ticket/close', 'Staff\\TicketController@close');
            // User
            $router->post('/user/update', 'Staff\\UserController@update');
            $router->get ('/user/getUserInfoById', 'Staff\\UserController@getUserInfoById');
            $router->post('/user/sendMail', 'Staff\\UserController@sendMail');
            $router->post('/user/ban', 'Staff\\UserController@ban');
            // Plan
            $router->get ('/plan/fetch', 'Staff\\PlanController@fetch');
            // Notice
            $router->get ('/notice/fetch', 'Admin\\NoticeController@fetch');
            $router->post('/notice/save', 'Admin\\NoticeController@save');
            $router->post('/notice/update', 'Admin\\NoticeController@update');
            $router->post('/notice/drop', 'Admin\\NoticeController@drop');
        });
    }
}
