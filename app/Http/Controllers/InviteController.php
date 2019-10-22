<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\InviteCode;
use App\Utils\Helper;

class InviteController extends Controller
{
    public function save (Request $request) {
        if (InviteCode::where('user_id', $request->session()->get('id'))->where('status', 0)->count() >= 5) {
            abort(500, '已达到创建数量上限');
        }
        $inviteCode = new InviteCode();
        $inviteCode->user_id = $request->session()->get('id');
        $inviteCode->code = Helper::randomChar(8);
        return response([
            'data' => $inviteCode->save()
        ]);
    }

    public function index (Request $request) {
        $codes = InviteCode::where('user_id', $request->session()->get('id'))
            ->where('status', 0)
            ->get();
        for ($i = 0; $i < count($codes); $i++) {
            $codes[$i]['invite_url'] = config('v2panel.app_url', env('APP_URL')) . '/#/register?code=' . $codes[$i]['code'];
        }
        $stat = [
            //已注册用户数
            User::where('invite_user_id', $request->session()->get('id'))->count(),
            //有效的佣金
            Order::where('status', 3)
                ->where('commission_status', 1)
                ->where('invite_user_id', $request->session()->get('id'))
                ->sum('commission_balance'),
            //确认中的佣金
            Order::where('status', 3)
                ->where('commission_status', 0)
                ->where('invite_user_id', $request->session()->get('id'))
                ->sum('commission_balance'),
            //已提现佣金
            0
            
        ];
        return response([
            'data' => [
                'codes' => $codes,
                'stat' => $stat
            ]
        ]);
    }
}
