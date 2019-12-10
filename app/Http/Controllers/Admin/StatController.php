<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ServerSave;
use App\Http\Requests\Admin\ServerUpdate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerGroup;
use App\Models\Server;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class StatController extends Controller
{
    public function dashboard (Request $request) {
        return response([
            'data' => [
                'month_income' => Redis::get('month_income'),
                'month_register_total' => Redis::get('month_register_total')
            ]
        ]);
    }
}
