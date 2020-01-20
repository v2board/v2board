<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Models\Server;
use App\Models\ServerLog;
use App\Utils\Helper;
use Illuminate\Support\Facades\Cache;

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
}
