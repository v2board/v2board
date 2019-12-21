<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Utils\Helper;
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
        if (\File::exists(base_path() . '/.lock')) {
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
		$this->info('正在导入数据库请稍等...');
		foreach($sql as $item) {
			try {
				DB::select(DB::raw($item));
			} catch (\Exception $e) {}
        }
        $email = '';
        while (!$email) {
        	$email = $this->ask('请输入管理员邮箱?');
        }
        $password = '';
        while (!$password) {
    		$password = $this->ask('请输入管理员密码?');
        }
        if (!$this->registerAdmin($email, $password)) {
        	abort(500, '管理员账号注册失败，请重试');
        }
        
		$this->info('一切就绪');
        \File::put(base_path() . '/.lock', time());
    }
    
    private function registerAdmin ($email, $password) {
        $user = new User();
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->v2ray_uuid = Helper::guid(true);
        $user->token = Helper::guid();
        $user->is_admin = 1;
        return $user->save();
    }
}
