<?php

namespace App\Console\Commands;

use App\Utils\CacheKey;
use Illuminate\Console\Command;
use App\Services\UserService;
use Illuminate\Support\Facades\Cache;

class CacheToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理用户';

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
        $userService = new UserService();
        $users = $userService->getAvailableUsers();
        foreach ($users as $user) {
            Cache::put(CacheKey::get('SUBSCRIBE_TOKEN', $user->token), 1, 120);
        }
    }
}
