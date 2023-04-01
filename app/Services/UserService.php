<?php

namespace App\Services;

use App\Jobs\StatServerJob;
use App\Jobs\StatUserJob;
use App\Jobs\TrafficFetchJob;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;

class UserService
{
    private function calcResetDayByMonthFirstDay()
    {
        $today = date('d');
        $lastDay = date('d', strtotime('last day of +0 months'));
        return $lastDay - $today;
    }

    private function calcResetDayByExpireDay(int $expiredAt)
    {
        $day = date('d', $expiredAt);
        $today = date('d');
        $lastDay = date('d', strtotime('last day of +0 months'));
        if ((int)$day >= (int)$today && (int)$day >= (int)$lastDay) {
            return $lastDay - $today;
        }
        if ((int)$day >= (int)$today) {
            return $day - $today;
        }

        return $lastDay - $today + $day;
    }

    private function calcResetDayByYearFirstDay(): int
    {
        $nextYear = strtotime(date("Y-01-01", strtotime('+1 year')));
        return (int)(($nextYear - time()) / 86400);
    }

    private function calcResetDayByYearExpiredAt(int $expiredAt): int
    {
        $md = date('m-d', $expiredAt);
        $nowYear = strtotime(date("Y-{$md}"));
        $nextYear = strtotime('+1 year', $nowYear);
        if ($nowYear > time()) {
            return (int)(($nowYear - time()) / 86400);
        }
        return (int)(($nextYear - time()) / 86400);
    }

    public function getResetDay(User $user)
    {
        if (!isset($user->plan)) {
            $user->plan = Plan::find($user->plan_id);
        }
        if ($user->expired_at <= time() || $user->expired_at === NULL) return null;
        // if reset method is not reset
        if ($user->plan->reset_traffic_method === 2) return null;
        switch (true) {
            case ($user->plan->reset_traffic_method === NULL): {
                $resetTrafficMethod = config('v2board.reset_traffic_method', 0);
                switch ((int)$resetTrafficMethod) {
                    // month first day
                    case 0:
                        return $this->calcResetDayByMonthFirstDay();
                    // expire day
                    case 1:
                        return $this->calcResetDayByExpireDay($user->expired_at);
                    // no action
                    case 2:
                        return null;
                    // year first day
                    case 3:
                        return $this->calcResetDayByYearFirstDay();
                    // year expire day
                    case 4:
                        return $this->calcResetDayByYearExpiredAt($user->expired_at);
                }
                break;
            }
            case ($user->plan->reset_traffic_method === 0): {
                return $this->calcResetDayByMonthFirstDay();
            }
            case ($user->plan->reset_traffic_method === 1): {
                return $this->calcResetDayByExpireDay($user->expired_at);
            }
            case ($user->plan->reset_traffic_method === 2): {
                return null;
            }
            case ($user->plan->reset_traffic_method === 3): {
                return $this->calcResetDayByYearFirstDay();
            }
            case ($user->plan->reset_traffic_method === 4): {
                return $this->calcResetDayByYearExpiredAt($user->expired_at);
            }
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

    public function trafficFetch(int $u, int $d, int $userId, array $server, string $protocol)
    {
        TrafficFetchJob::dispatch($u, $d, $userId, $server, $protocol);
        StatServerJob::dispatch($u, $d, $server, $protocol, 'd');
        StatUserJob::dispatch($u, $d, $userId, $server, $protocol, 'd');
    }
}
