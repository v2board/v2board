<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\StatUser;
use App\Services\StatisticalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function getTrafficLog(Request $request)
    {
        $statService = new StatisticalService();
        $statService->setStartAt(strtotime(date('Y-m-d')));
        $statService->setUserStats();
        $stats = StatUser::select([
            'u',
            'd',
            'record_at',
            'user_id',
            'server_rate'
        ])
            ->where('user_id', $request->user['id'])
            ->where('record_at', '>=', strtotime(date('Y-m-1')))
            ->orderBy('record_at', 'DESC')
            ->get()
            ->toArray();

        $stats = array_merge($stats, $statService->getStatUserByUserID($request->user['id']));
        usort($stats, function ($a, $b) {
            return $b['record_at'] - $a['record_at'];
        });

        return response([
            'data' => $stats
        ]);
    }
}
