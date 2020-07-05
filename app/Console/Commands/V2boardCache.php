<?php

namespace App\Console\Commands;

use App\Utils\CacheKey;
use Illuminate\Console\Command;
use App\Models\ServerLog;
use App\Models\ServerStat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class V2boardCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2board:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '缓存任务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    }

    private function cacheServerStat()
    {
        $serverLogs = ServerLog::select(
            'server_id',
            DB::raw("sum(u) as u"),
            DB::raw("sum(d) as d"),
            DB::raw("count(*) as online")
        )
            ->where('updated_at', '>=', time() - 3600)
            ->groupBy('server_id')
            ->get();
        foreach ($serverLogs as $serverLog) {
            $data = [
                'server_id' => $serverLog->server_id,
                'u' => $serverLog->u,
                'd' => $serverLog->d,
                'online' => $serverLog->online
            ];
//            ServerStat::create($data);
        }
    }
}
