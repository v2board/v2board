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
                    'invite_force' => (int)config('v2panel.invite_force', env('DEFAULT_INVITE_FORCE')),
                    'invite_commission' => config('v2panel.invite_commission', env('DEFAULT_INVITE_COMMISSION')),
                    'invite_gen_limit' => config('v2panel.invite_gen_limit', env('DEFAULT_INVITE_GEN_LIMIT'))
                ],
                'site' => [
                    'stop_register' => (int)config('v2panel.stop_register', env('DEFAULT_STOP_REGISTER')),
                    'email_verify' => (int)config('v2panel.email_verify', env('DEFAULT_EMAIL_VERIFY')),
                    'app_name' => config('v2panel.app_name', env('APP_NAME')),
                    'app_url' => config('v2panel.app_url', env('APP_URL'))
                ],
                'pay' => [
                    // alipay
                    'alipay_enable' => (int)config('v2panel.alipay_enable'),
                    'alipay_appid' => config('v2panel.alipay_appid'),
                    'alipay_pubkey' => config('v2panel.alipay_pubkey'),
                    'alipay_privkey' => config('v2panel.alipay_privkey'),
                    // stripe
                    'stripe_sk_live' => config('v2panel.stripe_sk_live'),
                    'stripe_pk_live' => config('v2panel.stripe_pk_live'),
                    'stripe_alipay_enable' => (int)config('v2panel.stripe_alipay_enable'),
                    'stripe_wepay_enable' => (int)config('v2panel.stripe_wepay_enable'),
                    'stripe_webhook_key' => config('v2panel.stripe_webhook_key')
                ],
                'server' => [
                    'server_token' => config('v2panel.server_token')
                ]
            ]
        ]);
    }
    
    public function save (ConfigSave $request) {
        $data = $request->input();
        $array = \Config::get('v2panel');
        foreach ($data as $k => $v) {
            if (!in_array($k, ConfigSave::filter())) {
                abort(500, '禁止修改');
            }
            $array[$k] = $v;
        }
        $data = var_export($array, 1);
        if(!\File::put(base_path() . '/config/v2panel.php', "<?php\n return $data ;")) {
            abort(500, '修改失败');
        }
        \Artisan::call('config:cache');
        return response([
            'data' => true
        ]);
    }
}
