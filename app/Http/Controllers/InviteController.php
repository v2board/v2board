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
        if (InviteCode::where('user_id', $request->session()->get('id'))->where('status', 0)->count() >= config('v2board.invite_gen_limit', 5)) {
            abort(500, '已达到创建数量上限');
        }
        $inviteCode = new InviteCode();
        $inviteCode->user_id = $request->session()->get('id');
        $inviteCode->code = Helper::randomChar(8);
        return response([
            'data' => $inviteCode->save()
        ]);
    }

    public function details (Request $request) {
        return response([
            'data' => Order::where('invite_user_id', $request->session()->get('id'))
                ->where('status', 3)
                ->select([
                    'id',
                    'commission_status',
                    'commission_balance',
                    'created_at',
                    'updated_at'
                ])
                ->get()
        ]);
    }

    public function fetch (Request $request) {
        $codes = InviteCode::where('user_id', $request->session()->get('id'))
            ->where('status', 0)
            ->get();
        $commission_rate = config('v2board.invite_commission');
        $user = User::find($request->session()->get('id'));
        if ($user->commission_rate) {
            $commission_rate = $user->commission_rate;
        }
        $stat = [
            //已注册用户数
            (int)User::where('invite_user_id', $request->session()->get('id'))->count(),
            //有效的佣金
            (int)Order::where('status', 3)
                ->where('commission_status', 1)
                ->where('invite_user_id', $request->session()->get('id'))
                ->sum('commission_balance'),
            //确认中的佣金
            (int)Order::where('status', 3)
                ->where('commission_status', 0)
                ->where('invite_user_id', $request->session()->get('id'))
                ->sum('commission_balance'),
            //佣金比例
            (int)$commission_rate
        ];
        return response([
            'data' => [
                'codes' => $codes,
                'stat' => $stat
            ]
        ]);
    }
}
