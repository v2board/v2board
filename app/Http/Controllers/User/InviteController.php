<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CommissionLog;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\InviteCode;
use App\Utils\Helper;

class InviteController extends Controller
{
    public function save(Request $request)
    {
        if (InviteCode::where('user_id', $request->user['id'])->where('status', 0)->count() >= config('v2board.invite_gen_limit', 5)) {
            abort(500, __('The maximum number of creations has been reached'));
        }
        $inviteCode = new InviteCode();
        $inviteCode->user_id = $request->user['id'];
        $inviteCode->code = Helper::randomChar(8);
        return response([
            'data' => $inviteCode->save()
        ]);
    }

    public function details(Request $request)
    {
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('page_size') >= 10 ? $request->input('page_size') : 10;
        $builder = CommissionLog::where('invite_user_id', $request->user['id'])
            ->where('get_amount', '>', 0)
            ->select([
                'id',
                'trade_no',
                'order_amount',
                'get_amount',
                'created_at'
            ])
            ->orderBy('created_at', 'DESC');
        $total = $builder->count();
        $details = $builder->forPage($current, $pageSize)
            ->get();
        return response([
            'data' => $details,
            'total' => $total
        ]);
    }

    public function fetch(Request $request)
    {
        $codes = InviteCode::where('user_id', $request->user['id'])
            ->where('status', 0)
            ->get();
        $commission_rate = config('v2board.invite_commission', 10);
        $user = User::find($request->user['id']);
        if ($user->commission_rate) {
            $commission_rate = $user->commission_rate;
        }
        $stat = [
            //Registered Users
            (int)User::where('invite_user_id', $request->user['id'])->count(),
            //Effective Commissions
            (int)Order::where('status', 3)
                ->where('commission_status', 2)
                ->where('invite_user_id', $request->user['id'])
                ->sum('commission_balance'),
            //Commissions under confirmation
            (int)Order::where('status', 3)
                ->where('commission_status', 0)
                ->where('invite_user_id', $request->user['id'])
                ->sum('commission_balance'),
            //Commission rate
            (int)$commission_rate,
            //Available Commissions
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
