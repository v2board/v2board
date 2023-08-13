<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionLog;
use App\Models\Order;
use App\Models\ServerShadowsocks;
use App\Models\ServerTrojan;
use App\Models\ServerVmess;
use App\Models\Stat;
use App\Models\StatServer;
use App\Models\StatUser;
use App\Models\Ticket;
use App\Models\User;
use App\Services\StatisticalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function override(Request $request)
    {
        $params = $request->validate([
            'start_at' => '',
            'end_at' => ''
        ]);

        if (isset($params['start_at']) && isset($params['end_at'])) {
            $stats = Stat::where('record_at', '>=', $params['start_at'])
                ->where('record_at', '<', $params['end_at'])
                ->get()
                ->makeHidden(['record_at', 'created_at', 'updated_at', 'id', 'record_type'])
                ->toArray();
        } else {
            $statisticalService = new StatisticalService();
            return [
                'data' => $statisticalService->generateStatData()
            ];
        }

        $stats = array_reduce($stats, function($carry, $item) {
            foreach($item as $key => $value) {
                if(isset($carry[$key]) && $carry[$key]) {
                    $carry[$key] += $value;
                } else {
                    $carry[$key] = $value;
                }
            }
            return $carry;
        }, []);

        return [
            'data' => $stats
        ];
    }

    public function record(Request $request)
    {
        $request->validate([
            'type' => 'required|in:paid_total,commission_total,register_count',
            'start_at' => '',
            'end_at' => ''
        ]);

        $statisticalService = new StatisticalService();
        $statisticalService->setStartAt($request->input('start_at'));
        $statisticalService->setEndAt($request->input('end_at'));
        return [
            'data' => $statisticalService->getStatRecord($request->input('type'))
        ];
    }

    public function ranking(Request $request)
    {
        $request->validate([
            'type' => 'required|in:server_traffic_rank,user_consumption_rank,invite_rank',
            'start_at' => '',
            'end_at' => '',
            'limit' => 'nullable|integer'
        ]);

        $statisticalService = new StatisticalService();
        $statisticalService->setStartAt($request->input('start_at'));
        $statisticalService->setEndAt($request->input('end_at'));
        return [
            'data' => $statisticalService->getRanking($request->input('type'), $request->input('limit') ?? 20)
        ];
    }
}

