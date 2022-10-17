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
            'u',
            'd',
            'record_at',
            'user_id',
            'server_rate'
        ])
            ->where('user_id', $request->user['id'])
            ->where('record_at', '>=', strtotime(date('Y-m-1')))
            ->orderBy('record_at', 'DESC');
        return response([
            'data' => $builder->get()
        ]);
    }
}
