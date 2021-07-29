<?php

namespace App\Services;

use App\Models\InviteCode;
use App\Models\Order;
use App\Models\Server;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
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

    public function trafficFetch(int $u, int $d, int $userId, object $server, string $protocol):bool
    {
        $user = User::lockForUpdate()
            ->find($userId);
        if (!$user) {
            return true;
        }
        $user->t = time();
        $user->u = $user->u + $u;
        $user->d = $user->d + $d;
        if (!$user->save()) {
            return false;
        }
        $mailService = new MailService();
        $serverService = new ServerService();
        try {
            $mailService->remindTraffic($user);
            $serverService->log(
                $userId,
                $server->id,
                $u,
                $d,
                $server->rate,
                $protocol
            );
        } catch (\Exception $e) {
        }
        return true;
    }
}
