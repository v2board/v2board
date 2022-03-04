<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\StatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function getTrafficLog(Request $request)
    {
        $builder = StatUser::select([
            DB::raw('sum(u) as u'),
            DB::raw('sum(d) as d'),
            'record_at',
            'user_id',
            'server_rate'
        ])
            ->where('user_id', $request->session()->get('id'))
            ->where('record_at', '>=', strtotime(date('Y-m-1')))
            ->groupBy('record_at', 'user_id', 'server_rate')
            ->orderBy('record_at', 'DESC');
        return response([
            'data' => $builder->get()
        ]);
    }
}
