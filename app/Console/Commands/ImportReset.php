<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Utils\Helper;

class ImportReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为导入用户重置所有uuid及token';

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
        $user = User::all();
        foreach ($user as $item) {
            $item->v2ray_uuid = Helper::guid(true);
            $item->token = Helper::guid();
            $item->save();
        }
    }
}
