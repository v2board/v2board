<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServerShadowsocks;
use App\Models\ServerTrojan;
use App\Services\ServerService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerGroup;
use App\Models\ServerVmess;
use App\Models\Plan;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Order;
use App\Models\StatOrder;
use App\Models\StatServer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\MetricsRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\Contracts\WorkloadRepository;
use Laravel\Horizon\WaitTimeCalculator;

class SystemController extends Controller
{
    public function getSystemStatus()
    {
        return response([
            'data' => [
                'schedule' => $this->getScheduleStatus(),
                'horizon' => $this->getHorizonStatus()
            ]
        ]);
    }

    public function getQueueWorkload(WorkloadRepository $workload)
    {
        return response([
            'data' => collect($workload->get())->sortBy('name')->values()->toArray()
        ]);
    }

    protected function getScheduleStatus():bool
    {
        return (time() - 120) < Cache::get(CacheKey::get('SCHEDULE_LAST_CHECK_AT', null));
    }

    protected function getHorizonStatus():bool
    {
        if (! $masters = app(MasterSupervisorRepository::class)->all()) {
            return false;
        }

        return collect($masters)->contains(function ($master) {
            return $master->status === 'paused';
        }) ? false : true;
    }

    public function getQueueStats()
    {
        return response([
            'data' => [
                'failedJobs' => app(JobRepository::class)->countRecentlyFailed(),
                'jobsPerMinute' => app(MetricsRepository::class)->jobsProcessedPerMinute(),
                'pausedMasters' => $this->totalPausedMasters(),
                'periods' => [
                    'failedJobs' => config('horizon.trim.recent_failed', config('horizon.trim.failed')),
                    'recentJobs' => config('horizon.trim.recent'),
                ],
                'processes' => $this->totalProcessCount(),
                'queueWithMaxRuntime' => app(MetricsRepository::class)->queueWithMaximumRuntime(),
                'queueWithMaxThroughput' => app(MetricsRepository::class)->queueWithMaximumThroughput(),
                'recentJobs' => app(JobRepository::class)->countRecent(),
                'status' => $this->getHorizonStatus(),
                'wait' => collect(app(WaitTimeCalculator::class)->calculate())->take(1),
            ]
        ]);
    }

    /**
     * Get the total process count across all supervisors.
     *
     * @return int
     */
    protected function totalProcessCount()
    {
        $supervisors = app(SupervisorRepository::class)->all();

        return collect($supervisors)->reduce(function ($carry, $supervisor) {
            return $carry + collect($supervisor->processes)->sum();
        }, 0);
    }

    /**
     * Get the number of master supervisors that are currently paused.
     *
     * @return int
     */
    protected function totalPausedMasters()
    {
        if (! $masters = app(MasterSupervisorRepository::class)->all()) {
            return 0;
        }

        return collect($masters)->filter(function ($master) {
            return $master->status === 'paused';
        })->count();
    }
}

