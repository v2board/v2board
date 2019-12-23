<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerGroup;
use App\Models\Server;
use App\Models\Plan;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Order;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    public function override (Request $request) {
        return response([
            'data' => [
                'month_income' => Redis::get('month_income'),
                'month_register_total' => Redis::get('month_register_total'),
                'ticket_pendding_total' => Ticket::where('status', 0)
                    ->count(),
                'commission_pendding_total' => Order::where('commission_status', 0)
                    ->where('invite_user_id', '!=', NULL)
                    ->where('status', 3)
                    ->count(),
                
            ]
        ]);
    }
}
