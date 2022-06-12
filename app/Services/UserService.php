<?php

namespace App\Services;

use App\Jobs\ServerLogJob;
use App\Jobs\StatServerJob;
use App\Jobs\StatUserJob;
use App\Jobs\TrafficFetchJob;
use App\Models\InviteCode;
use App\Models\Order;
use App\Models\ServerV2ray;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function getResetDay(User $user)
    {
        if ($user->expired_at <= time() || $user->expired_at === NULL) return null;
        // if reset method is not reset
        if (isset($user->plan->reset_traffic_method) && $user->plan->reset_traffic_method === 2) return null;

        if ((int)config('v2board.reset_traffic_method') === 0 ||
            (isset($user->plan->reset_traffic_method) && $user->plan->reset_traffic_method === 0))
        {
            $day = date('d', $user->expired_at);
            $today = date('d');
            $lastDay = date('d', strtotime('last day of +0 months'));
            return $lastDay - $today;
        }
        if ((int)config('v2board.reset_traffic_method') === 1 ||
            (isset($user->plan->reset_traffic_method) && $user->plan->reset_traffic_method === 1))
        {
            $day = date('d', $user->expired_at);
            $today = date('d');
            $lastDay = date('d', strtotime('last day of +0 months'));
            if ((int)$day >= (int)$today && (int)$day >= (int)$lastDay) {
                return $lastDay - $today;
            }
            if ((int)$day >= (int)$today) {
                return $day - $today;
            } else {
                return $lastDay - $today + $day;
            }
        }
        if ((int)config('v2board.reset_traffic_method') === 3 ||
            (isset($user->plan->reset_traffic_method) && $user->plan->reset_traffic_method === 3))
        {
            $nextYear = strtotime(date("Y-01-01", strtotime('+1 year')));
            return (int)(($nextYear - time()) / 86400);
        }
        if ((int)config('v2board.reset_traffic_method') === 4 ||
            (isset($user->plan->reset_traffic_method) && $user->plan->reset_traffic_method === 4))
        {
            $md = date('m-d', $user->expired_at);
            $nowYear = strtotime(date("Y-{$md}"));
            $nextYear = strtotime('+1 year', $nowYear);
            return (int)(($nextYear - time()) / 86400);
        }
        return null;
    }

    public function isAvailable(User $user)
    {
        if (!$user->banned && $user->transfer_enable && ($user->expired_at > time() || $user->expired_at === NULL)) {
            return true;
        }
        return false;
    }

    public function getAvailableUsers()
    {
        return User::whereRaw('u + d < transfer_enable')
            ->where(function ($query) {
                $query->where('expired_at', '>=', time())
                    ->orWhere('expired_at', NULL);
            })
            ->where('banned', 0)
            ->get();
    }

    public function getUnAvailbaleUsers()
    {
        return User::where(function ($query) {
            $query->where('expired_at', '<', time())
                ->orWhere('expired_at', 0);
        })
            ->where(function ($query) {
            $query->where('plan_id', NULL)
                ->orWhere('transfer_enable', 0);
        })
            ->get();
    }

    public function getUsersByIds($ids)
    {
        return User::whereIn('id', $ids)->get();
    }

    public function getAllUsers()
    {
        return User::all();
    }

    public function addBalance(int $userId, int $balance):bool
    {
        $user = User::lockForUpdate()->find($userId);
        if (!$user) {
            return false;
        }
        $user->balance = $user->balance + $balance;
        if ($user->balance < 0) {
            return false;
        }
        if (!$user->save()) {
            return false;
        }
        return true;
    }

    public function isNotCompleteOrderByUserId(int $userId):bool
    {
        $order = Order::whereIn('status', [0, 1])
            ->where('user_id', $userId)
            ->first();
        if (!$order) {
            return false;
        }
        return true;
    }

    public function trafficFetch(int $u, int $d, int $userId, object $server, string $protocol)
    {
        TrafficFetchJob::dispatch($u, $d, $userId, $server, $protocol);
        StatServerJob::dispatch($u, $d, $server, $protocol, 'd');
        StatUserJob::dispatch($u, $d, $userId, $server, $protocol, 'd');
    }
}
