<?php
namespace App\Services;

use App\Models\CommissionLog;
use App\Models\Order;
use App\Models\Stat;
use App\Models\StatServer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticalService {
    protected $userStats;
    protected $startAt;
    protected $endAt;
    protected $serverStats;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    public function setStartAt($timestamp) {
        $this->startAt = $timestamp;
    }

    public function setEndAt($timestamp) {
        $this->endAt = $timestamp;
    }

    public function setServerStats() {
        $this->serverStats = Cache::get("stat_server_{$this->startAt}");
        $this->serverStats = json_decode($this->serverStats, true) ?? [];
        if (!is_array($this->serverStats)) {
            $this->serverStats = [];
        }
    }

    public function setUserStats() {
        $this->userStats = Cache::get("stat_user_{$this->startAt}");
        $this->userStats = json_decode($this->userStats, true) ?? [];
        if (!is_array($this->userStats)) {
            $this->userStats = [];
        }
    }

    public function generateStatData(): array
    {
        $startAt = $this->startAt;
        $endAt = $this->endAt;
        if (!$startAt || !$endAt) {
            $startAt = strtotime(date('Y-m-d'));
            $endAt = strtotime('+1 day', $startAt);
        }
        $data = [];
        $data['order_count'] = Order::where('created_at', '>=', $startAt)
            ->where('created_at', '<', $endAt)
            ->count();
        $data['order_total'] = Order::where('created_at', '>=', $startAt)
            ->where('created_at', '<', $endAt)
            ->sum('total_amount');
        $data['paid_count'] = Order::where('paid_at', '>=', $startAt)
            ->where('paid_at', '<', $endAt)
            ->whereNotIn('status', [0, 2])
            ->count();
        $data['paid_total'] = Order::where('paid_at', '>=', $startAt)
            ->where('paid_at', '<', $endAt)
            ->whereNotIn('status', [0, 2])
            ->sum('total_amount');
        $commissionLogBuilder = CommissionLog::where('created_at', '>=', $startAt)
            ->where('created_at', '<', $endAt);
        $data['commission_count'] = $commissionLogBuilder->count();
        $data['commission_total'] = $commissionLogBuilder->sum('get_amount');
        $data['register_count'] = User::where('created_at', '>=', $startAt)
            ->where('created_at', '<', $endAt)
            ->count();
        $data['invite_count'] = User::where('created_at', '>=', $startAt)
            ->where('created_at', '<', $endAt)
            ->whereNotNull('invite_user_id')
            ->count();
        $data['transfer_used_total'] = StatServer::where('created_at', '>=', $startAt)
                ->where('created_at', '<', $endAt)
                ->select(DB::raw('SUM(u) + SUM(d) as total'))
                ->value('total') ?? 0;
        return $data;
    }

    public function statServer($serverId, $serverType, $data)
    {
        foreach (array_keys($data) as $key) {
            $this->serverStats[$serverType] = $this->serverStats[$serverType] ?? [];
            if (isset($this->serverStats[$serverType][$serverId])) {
                $this->serverStats[$serverType][$serverId][0] += $data[$key][0];
                $this->serverStats[$serverType][$serverId][1] += $data[$key][1];
            } else {
                $this->serverStats[$serverType][$serverId] = $data[$key];
            }
        }
        Cache::set("stat_server_{$this->startAt}", json_encode($this->serverStats));
    }

    public function statUser($rate, $data)
    {
        foreach (array_keys($data) as $key) {
            $this->userStats[$rate] = $this->userStats[$rate] ?? [];
            if (isset($this->userStats[$rate][$key])) {
                $this->userStats[$rate][$key][0] += $data[$key][0];
                $this->userStats[$rate][$key][1] += $data[$key][1];
            } else {
                $this->userStats[$rate][$key] = $data[$key];
            }
        }
        Cache::set("stat_user_{$this->startAt}", json_encode($this->userStats));
    }

    public function getStatUserByUserID($userId): array
    {
        $stats = [];
        foreach (array_keys($this->userStats) as $rate) {
            if (!isset($this->userStats[$rate][$userId])) continue;
            $stats[] = [
                'record_at' => $this->startAt,
                'server_rate' => $rate,
                'u' => $this->userStats[$rate][$userId][0],
                'd' => $this->userStats[$rate][$userId][1],
                'user_id' => $userId
            ];
        }
        return $stats;
    }

    public function getStatUser()
    {
        $stats = [];
        foreach ($this->userStats as $k => $v) {
            foreach (array_keys($v) as $userId) {
                if (isset($v[$userId])) {
                    $stats[] = [
                        'server_rate' => $k,
                        'u' => $v[$userId][0],
                        'd' => $v[$userId][1],
                        'user_id' => $userId
                    ];
                }
            }
        }
        return $stats;
    }


    public function getStatServer()
    {
        $stats = [];
        foreach ($this->serverStats as $serverType => $v) {
            foreach (array_keys($v) as $serverId) {
                if (isset($v[$serverId])) {
                    $stats[] = [
                        'server_id' => $serverId,
                        'server_type' => $serverType,
                        'u' => $v[$serverId][0],
                        'd' => $v[$serverId][1],
                    ];
                }
            }
        }
        return $stats;
    }

    public function clearStatUser()
    {
        Cache::forget("stat_user_{$this->startAt}");
    }

    public function clearStatServer()
    {
        Cache::forget("stat_server_{$this->startAt}");
    }

    public function getStatRecord($type)
    {
        switch ($type) {
            case "order_total": {
                return Stat::select([
                    '*',
                    DB::raw('order_total / 100 as order_total')
                ])
                    ->where('record_at', '>=', $this->startAt)
                    ->where('record_at', '<', $this->endAt)
                    ->orderBy('record_at', 'ASC')
                    ->get();
            }
            case "commission_total": {
                return Stat::select([
                    '*',
                    DB::raw('commission_total / 100 as commission_total')
                ])
                    ->where('record_at', '>=', $this->startAt)
                    ->where('record_at', '<', $this->endAt)
                    ->orderBy('record_at', 'ASC')
                    ->get();
            }
            case "register_count": {
                return Stat::where('record_at', '>=', $this->startAt)
                    ->where('record_at', '<', $this->endAt)
                    ->orderBy('record_at', 'ASC')
                    ->get();
            }
        }
    }
}
