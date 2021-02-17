<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserTransfer;
use App\Http\Requests\User\UserUpdate;
use App\Http\Requests\User\UserChangePassword;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;
use App\Models\Ticket;
use App\Utils\Helper;
use App\Models\Order;
use App\Models\ServerLog;

class UserController extends Controller
{
    public function logout(Request $request)
    {
        $request->session()->flush();
        return response([
            'data' => true
        ]);
    }

    public function changePassword(UserChangePassword $request)
    {
        $user = User::find($request->session()->get('id'));
        if (!$user) {
            abort(500, __('user.user.changePassword.user_not_exist'));
        }
        if (!Helper::multiPasswordVerify(
            $user->password_algo,
            $request->input('old_password'),
            $user->password)
        ) {
            abort(500, __('user.user.changePassword.old_password_wrong'));
        }
        $user->password = password_hash($request->input('new_password'), PASSWORD_DEFAULT);
        $user->password_algo = NULL;
        if (!$user->save()) {
            abort(500, __('user.user.changePassword.save_failed'));
        }
        $request->session()->flush();
        return response([
            'data' => true
        ]);
    }

    public function info(Request $request)
    {
        $user = User::where('id', $request->session()->get('id'))
            ->select([
                'email',
                'transfer_enable',
                'last_login_at',
                'created_at',
                'banned',
                'remind_expire',
                'remind_traffic',
                'expired_at',
                'balance',
                'commission_balance',
                'plan_id',
                'discount',
                'commission_rate',
                'telegram_id'
            ])
            ->first();
        if (!$user) {
            abort(500, __('user.user.info.user_not_exist'));
        }
        $user['avatar_url'] = 'https://cdn.v2ex.com/gravatar/' . md5($user->email) . '?s=64&d=identicon';
        return response([
            'data' => $user
        ]);
    }

    public function getStat(Request $request)
    {
        $stat = [
            Order::where('status', 0)
                ->where('user_id', $request->session()->get('id'))
                ->count(),
            Ticket::where('status', 0)
                ->where('user_id', $request->session()->get('id'))
                ->count(),
            User::where('invite_user_id', $request->session()->get('id'))
                ->count()
        ];
        return response([
            'data' => $stat
        ]);
    }

    public function getSubscribe(Request $request)
    {
        $user = User::where('id', $request->session()->get('id'))
            ->select([
                'id',
                'plan_id',
                'token',
                'expired_at',
                'u',
                'd',
                'transfer_enable',
                'email'
            ])
            ->first();
        if (!$user) {
            abort(500, __('user.user.getSubscribe.user_not_exist'));
        }
        if ($user->plan_id) {
            $user['plan'] = Plan::find($user->plan_id);
            if (!$user['plan']) {
                abort(500, __('user.user.getSubscribe.plan_not_exist'));
            }
        }
        $user['subscribe_url'] = config('v2board.subscribe_url', config('v2board.app_url', env('APP_URL'))) . '/api/v1/client/subscribe?token=' . $user['token'];
        $user['reset_day'] = $this->getResetDay($user);
        return response([
            'data' => $user
        ]);
    }

    public function resetSecurity(Request $request)
    {
        $user = User::find($request->session()->get('id'));
        if (!$user) {
            abort(500, __('user.user.resetSecurity.user_not_exist'));
        }
        $user->uuid = Helper::guid(true);
        $user->token = Helper::guid();
        if (!$user->save()) {
            abort(500, __('user.user.resetSecurity.reset_failed'));
        }
        return response([
            'data' => config('v2board.subscribe_url', config('v2board.app_url', env('APP_URL'))) . '/api/v1/client/subscribe?token=' . $user->token
        ]);
    }

    public function update(UserUpdate $request)
    {
        $updateData = $request->only([
            'remind_expire',
            'remind_traffic'
        ]);

        $user = User::find($request->session()->get('id'));
        if (!$user) {
            abort(500, __('user.user.update.user_not_exist'));
        }
        try {
            $user->update($updateData);
        } catch (\Exception $e) {
            abort(500, __('user.user.update.save_failed'));
        }

        return response([
            'data' => true
        ]);
    }

    public function transfer(UserTransfer $request)
    {
        $user = User::find($request->session()->get('id'));
        if (!$user) {
            abort(500, __('user.user.transfer.user_not_exist'));
        }
        if ($request->input('transfer_amount') > $user->commission_balance) {
            abort(500, __('user.user.transfer.insufficient_commission_balance'));
        }
        $user->commission_balance = $user->commission_balance - $request->input('transfer_amount');
        $user->balance = $user->balance + $request->input('transfer_amount');
        if (!$user->save()) {
            abort(500, __('user.user.transfer.transfer_failed'));
        }
        return response([
            'data' => true
        ]);
    }

    private function getResetDay(User $user)
    {
        if ($user->expired_at <= time() || $user->expired_at === NULL) return null;
        $day = date('d', $user->expired_at);
        $today = date('d');
        $lastDay = date('d', strtotime('last day of +0 months'));

        if ((int)config('v2board.reset_traffic_method') === 0) {
            return $lastDay - $today;
        }
        if ((int)config('v2board.reset_traffic_method') === 1) {
            if ((int)$day >= (int)$today && (int)$day >= (int)$lastDay) {
                return $lastDay - $today;
            }
            if ((int)$day >= (int)$today) {
                return $day - $today;
            } else {
                return $lastDay - $today + $day;
            }
        }
        return null;
    }
}
