<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ConfigSave;
use Illuminate\Http\Request;
use App\Utils\Dict;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function fetch()
    {
        // TODO: default should be in Dict
        return response([
            'data' => [
                'invite' => [
                    'invite_force' => (int)config('v2board.invite_force', 0),
                    'invite_commission' => config('v2board.invite_commission', 10),
                    'invite_gen_limit' => config('v2board.invite_gen_limit', 5),
                    'invite_never_expire' => config('v2board.invite_never_expire', 0)
                ],
                'site' => [
                    'safe_mode_enable' => (int)config('v2board.safe_mode_enable', 0),
                    'stop_register' => (int)config('v2board.stop_register', 0),
                    'email_verify' => (int)config('v2board.email_verify', 0),
                    'app_name' => config('v2board.app_name', 'V2Board'),
                    'app_description' => config('v2board.app_description', 'V2Board is best!'),
                    'app_url' => config('v2board.app_url'),
                    'subscribe_url' => config('v2board.subscribe_url'),
                    'plan_change_enable' => (int)config('v2board.plan_change_enable', 1),
                    'try_out_plan_id' => (int)config('v2board.try_out_plan_id', 0),
                    'try_out_hour' => (int)config('v2board.try_out_hour', 1),
                    'email_whitelist_enable' => (int)config('v2board.email_whitelist_enable', 0),
                    'email_whitelist_suffix' => config('v2board.email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT)
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
                    'stripe_webhook_key' => config('v2board.stripe_webhook_key'),
                    // bitpayx
                    'bitpayx_enable' => config('v2board.bitpayx_enable'),
                    'bitpayx_appsecret' => config('v2board.bitpayx_appsecret'),
                    // paytaro
                    'paytaro_enable' => config('v2board.paytaro_enable'),
                    'paytaro_app_id' => config('v2board.paytaro_app_id'),
                    'paytaro_app_secret' => config('v2board.paytaro_app_secret')
                ],
                'frontend' => [
                    'frontend_theme_sidebar' => config('v2board.frontend_theme_sidebar', 'light'),
                    'frontend_theme_header' => config('v2board.frontend_theme_header', 'dark'),
                    'frontend_theme_color' => config('v2board.frontend_theme_color', 'default'),
                    'frontend_background_url' => config('v2board.frontend_background_url')
                ],
                'server' => [
                    'server_token' => config('v2board.server_token'),
                    'server_license' => config('v2board.server_license')
                ],
                'tutorial' => [
                    'apple_id' => config('v2board.apple_id')
                ]
            ]
        ]);
    }

    public function save(ConfigSave $request)
    {
        $data = $request->input();
        $array = \Config::get('v2board');
        foreach ($data as $k => $v) {
            if (!in_array($k, ConfigSave::filter())) {
                abort(500, '参数' . $k . '不在规则内，禁止修改');
            }
            $array[$k] = $v;
        }
        $data = var_export($array, 1);
        if (!\File::put(base_path() . '/config/v2board.php', "<?php\n return $data ;")) {
            abort(500, '修改失败');
        }
        \Artisan::call('config:cache');
        return response([
            'data' => true
        ]);
    }
}
