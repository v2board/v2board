<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\InviteCode;
use App\Utils\Helper;

class InviteController extends Controller
{
    public function save(Request $request)
    {
        if (InviteCode::where('user_id', $request->session()->get('id'))->where('status', 0)->count() >= config('v2board.invite_gen_limit', 5)) {
            abort(500, __('The maximum number of creations has been reached'));
        }
        $inviteCode = new InviteCode();
        $inviteCode->user_id = $request->session()->get('id');
        $inviteCode->code = Helper::randomChar(8);
        return response([
            'data' => $inviteCode->save()
        ]);
    }

    public function details(Request $request)
    {
        return response([
            'data' => Order::where('invite_user_id', $request->session()->get('id'))
                ->where('commission_balance', '>', 0)
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

    public function fetch(Request $request)
    {
        $codes = InviteCode::where('user_id', $request->session()->get('id'))
            ->where('status', 0)
            ->get();
        $commission_rate = config('v2board.invite_commission', 10);
        $user = User::find($request->session()->get('id'));
        if ($user->commission_rate) {
            $commission_rate = $user->commission_rate;
        }
        $stat = [
            //已注册用户数
            (int)User::where('invite_user_id', $request->session()->get('id'))->count(),
            //有效的佣金
            (int)Order::where('status', 3)
                ->where('commission_status', 2)
                ->where('invite_user_id', $request->session()->get('id'))
                ->sum('commission_balance'),
            //确认中的佣金
            (int)Order::where('status', 3)
                ->where('commission_status', 0)
                ->where('invite_user_id', $request->session()->get('id'))
                ->sum('commission_balance'),
            //佣金比例
            (int)$commission_rate,
            //可用佣金
            (int)$user->commission_balance
        ];
        return response([
            'data' => [
                'codes' => $codes,
                'stat' => $stat
            ]
        ]);
    }
}
