<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServerShadowsocks;
use App\Models\ServerTrojan;
use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerGroup;
use App\Models\Server;
use App\Models\Plan;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Order;
use App\Models\StatOrder;
use App\Models\StatServer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function getOverride(Request $request)
    {
        return response([
            'data' => [
                'month_income' => Order::where('created_at', '>=', strtotime(date('Y-m-1')))
                    ->where('created_at', '<', time())
                    ->whereNotIn('status', [0, 2])
                    ->sum('total_amount'),
                'month_register_total' => User::where('created_at', '>=', strtotime(date('Y-m-1')))
                    ->where('created_at', '<', time())
                    ->count(),
                'ticket_pendding_total' => Ticket::where('status', 0)
                    ->count(),
                'commission_pendding_total' => Order::where('commission_status', 0)
                    ->where('invite_user_id', '!=', NULL)
                    ->whereNotIn('status', [0, 2])
                    ->where('commission_balance', '>', 0)
                    ->count(),
                'day_income' => Order::where('created_at', '>=', strtotime(date('Y-m-d')))
                    ->where('created_at', '<', time())
                    ->whereNotIn('status', [0, 2])
                    ->sum('total_amount'),
                'last_month_income' => Order::where('created_at', '>=', strtotime('-1 month', strtotime(date('Y-m-1'))))
                    ->where('created_at', '<', strtotime(date('Y-m-1')))
                    ->whereNotIn('status', [0, 2])
                    ->sum('total_amount')
            ]
        ]);
    }

    public function getOrder(Request $request)
    {
        $statistics = StatOrder::where('record_type', 'd')
            ->limit(31)
            ->orderBy('record_at', 'DESC')
            ->get()
            ->toArray();
        $result = [];
        foreach ($statistics as $statistic) {
            $date = date('m-d', $statistic['record_at']);
            array_push($result, [
                'type' => '收款金额',
                'date' => $date,
                'value' => $statistic['order_amount'] / 100
            ]);
            array_push($result, [
                'type' => '收款笔数',
                'date' => $date,
                'value' => $statistic['order_count']
            ]);
            array_push($result, [
                'type' => '佣金金额',
                'date' => $date,
                'value' => $statistic['commission_amount'] / 100
            ]);
            array_push($result, [
                'type' => '佣金笔数',
                'date' => $date,
                'value' => $statistic['commission_count']
            ]);
        }
        $result = array_reverse($result);
        return response([
            'data' => $result
        ]);
    }

    public function getServerLastRank()
    {
        $servers = [
            'shadowsocks' => ServerShadowsocks::where('parent_id', null)->get()->toArray(),
            'vmess' => Server::where('parent_id', null)->get()->toArray(),
            'trojan' => ServerTrojan::where('parent_id', null)->get()->toArray()
        ];
        $timestamp = strtotime('-1 day', strtotime(date('Y-m-d')));
        $statistics = StatServer::select([
                'server_id',
                'server_type',
                'u',
                'd',
                DB::raw('(u+d) as total')
            ])
            ->where('record_at', '>=', $timestamp)
            ->where('record_type', 'd')
            ->limit(10)
            ->orderBy('total', 'DESC')
            ->get()
            ->toArray();
        foreach ($statistics as $k => $v) {
            foreach ($servers[$v['server_type']] as $server) {
                if ($server['id'] === $v['server_id']) {
                    $statistics[$k]['server_name'] = $server['name'];
                }
            }
            $statistics[$k]['total'] = $statistics[$k]['total'] / 1073741824;
        }
        array_multisort(array_column($statistics, 'total'), SORT_DESC, $statistics);
        return response([
            'data' => $statistics
        ]);
    }
}

