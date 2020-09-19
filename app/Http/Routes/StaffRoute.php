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
        });
    }
}
