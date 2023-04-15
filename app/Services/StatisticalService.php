<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;

class StatisticalService {
    protected $userStats;
    protected $recordAt;

    public function __construct($recordAt = '')
    {
        ini_set('memory_limit', -1);
        $this->recordAt = $recordAt ?? strtotime(date('Y-m-d'));
        $this->userStats = Cache::get("stat_user_{$this->recordAt}");
        $this->userStats = json_decode($this->userStats, true) ?? [];
        if (!is_array($this->userStats)) {
            $this->userStats = [];
        }
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
        Cache::set("stat_user_{$this->recordAt}", json_encode($this->userStats));
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
}
