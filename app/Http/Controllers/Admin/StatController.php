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
use Illuminate\Support\Facades\Cache;

class StatController extends Controller
{
    public function getOverride(Request $request)
    {
        return response([
            'data' => [
                'month_income' => Order::where('created_at', '>=', strtotime(date('Y-m-1')))
                    ->where('created_at', '<', time())
                    ->whereIn('status', [3, 4])
                    ->sum('total_amount'),
                'month_register_total' => User::where('created_at', '>=', strtotime(date('Y-m-1')))
                    ->where('created_at', '<', time())
                    ->count(),
                'ticket_pendding_total' => Ticket::where('status', 0)
                    ->count(),
                'commission_pendding_total' => Order::where('commission_status', 0)
                    ->where('invite_user_id', '!=', NULL)
                    ->where('status', [3, 4])
                    ->where('commission_balance', '>', 0)
                    ->count(),
                'day_income' => Order::where('created_at', '>=', strtotime(date('Y-m-d')))
                    ->where('created_at', '<', time())
                    ->whereIn('status', [3, 4])
                    ->sum('total_amount'),
                'last_month_income' => Order::where('created_at', '>=', strtotime('-1 month', strtotime(date('Y-m-1'))))
                    ->where('created_at', '<', strtotime(date('Y-m-1')))
                    ->whereIn('status', [3, 4])
                    ->sum('total_amount')
            ]
        ]);
    }
}
