<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ConfigSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Order;
use App\Models\User;

class ConfigController extends Controller
{
    public function init () {

    }

    public function index () {
        return response([
            'data' => [
                'invite' => [
                    'invite_force' => (int)config('v2board.invite_force', env('DEFAULT_INVITE_FORCE')),
                    'invite_commission' => config('v2board.invite_commission', env('DEFAULT_INVITE_COMMISSION')),
                    'invite_gen_limit' => config('v2board.invite_gen_limit', env('DEFAULT_INVITE_GEN_LIMIT'))
                ],
                'site' => [
                    'stop_register' => (int)config('v2board.stop_register', env('DEFAULT_STOP_REGISTER')),
                    'email_verify' => (int)config('v2board.email_verify', env('DEFAULT_EMAIL_VERIFY')),
                    'app_name' => config('v2board.app_name', env('APP_NAME')),
                    'app_url' => config('v2board.app_url', env('APP_URL'))
                ],
                'pay' => [
                    // alipay
                    'alipay_enable' => (int)config('v2board.alipay_enable'),
                    'alipay_appid' => config('v2board.alipay_appid'),
                    'alipay_pubkey' => config('v2board.alipay_pubkey'),
                    'alipay_privkey' => config('v2board.alipay_privkey'),
                    // stripe
                    'stripe_sk_live' => config('v2board.stripe_sk_live'),
                    'stripe_pk_live' => config('v2board.stripe_pk_live'),
                    'stripe_alipay_enable' => (int)config('v2board.stripe_alipay_enable'),
                    'stripe_wepay_enable' => (int)config('v2board.stripe_wepay_enable'),
                    'stripe_webhook_key' => config('v2board.stripe_webhook_key')
                ],
                'server' => [
                    'server_token' => config('v2board.server_token')
                ]
            ]
        ]);
    }
    
    public function save (ConfigSave $request) {
        $data = $request->input();
        $array = \Config::get('v2board');
        foreach ($data as $k => $v) {
            if (!in_array($k, ConfigSave::filter())) {
                abort(500, '禁止修改');
            }
            $array[$k] = $v;
        }
        $data = var_export($array, 1);
        if(!\File::put(base_path() . '/config/v2board.php', "<?php\n return $data ;")) {
            abort(500, '修改失败');
        }
        \Artisan::call('config:cache');
        return response([
            'data' => true
        ]);
    }
}
