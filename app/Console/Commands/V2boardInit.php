<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class V2boardInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2board:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'v2board 初始化';

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
        if (\File::get(base_path() . '/.lock')) {
            abort(500, 'V2board 已安装，如需重新初始化请删除目录下.lock文件');
        }
        \Artisan::call('key:generate');
        \Artisan::call('config:cache');
    	DB::connection()->getPdo();
    	$file = \File::get(base_path() . '/install.sql');
    	if (!$file) {
    		abort(500, '数据库文件不存在');
    	}
		$sql = str_replace("\n", "", $file);
		$sql = preg_split("/;/", $sql);
		if (!is_array($sql)) {
			abort(500, '数据库文件格式有误');
		}
		foreach($sql as $item) {
			echo 'RUN ' . $item . "\r\n";
			try {
				DB::select(DB::raw($item));
			} catch (\Exception $e) {}
        }
        \File::put(base_path() . '/.lock', time());
    }
}
