<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ConfigSave;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Utils\Dict;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function getEmailTemplate()
    {
        $path = resource_path('views/mail/');
        $files = array_map(function ($item) use ($path) {
            return str_replace($path, '', $item);
        }, glob($path . '*'));
        return response([
            'data' => $files
        ]);
    }

    public function setTelegramWebhook(Request $request)
    {
        $telegramService = new TelegramService($request->input('telegram_bot_token'));
        $telegramService->getMe();
        $telegramService->setWebhook(
            url(
                '/api/v1/guest/telegram/webhook?access_token=' . md5(config('v2board.telegram_bot_token', $request->input('telegram_bot_token')))
            )
        );
        return response([
            'data' => true
        ]);
    }

    public function fetch()
    {
        // TODO: default should be in Dict
        return response([
            'data' => [
                'invite' => [
                    'invite_force' => (int)config('v2board.invite_force', 0),
                    'invite_commission' => config('v2board.invite_commission', 10),
                    'invite_gen_limit' => config('v2board.invite_gen_limit', 5),
                    'invite_never_expire' => config('v2board.invite_never_expire', 0),
                    'commission_first_time_enable' => config('v2board.commission_first_time_enable', 1),
                    'commission_auto_check_enable' => config('v2board.commission_auto_check_enable', 1)
                ],
                'site' => [
                    'safe_mode_enable' => (int)config('v2board.safe_mode_enable', 0),
                    'stop_register' => (int)config('v2board.stop_register', 0),
                    'email_verify' => (int)config('v2board.email_verify', 0),
                    'app_name' => config('v2board.app_name', 'V2Board'),
                    'app_description' => config('v2board.app_description', 'V2Board is best!'),
                    'app_url' => config('v2board.app_url'),
                    'subscribe_url' => config('v2board.subscribe_url'),
                    'try_out_plan_id' => (int)config('v2board.try_out_plan_id', 0),
                    'try_out_hour' => (int)config('v2board.try_out_hour', 1),
                    'email_whitelist_enable' => (int)config('v2board.email_whitelist_enable', 0),
                    'email_whitelist_suffix' => config('v2board.email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT),
                    'email_gmail_limit_enable' => config('v2board.email_gmail_limit_enable', 0)
                ],
                'subscribe' => [
                    'plan_change_enable' => (int)config('v2board.plan_change_enable', 1),
                    'reset_traffic_method' => (int)config('v2board.reset_traffic_method', 0),
                    'renew_reset_traffic_enable' => (int)config('v2board.renew_reset_traffic_enable', 1)
                ],
                'pay' => [
                    // alipay
                    'alipay_enable' => (int)config('v2board.alipay_enable'),
                    'alipay_appid' => config('v2board.alipay_appid'),
                    'alipay_pubkey' => config('v2board.alipay_pubkey'),
                    'alipay_privkey' => config('v2board.alipay_privkey'),
                    // stripe
                    'stripe_alipay_enable' => (int)config('v2board.stripe_alipay_enable', 0),
                    'stripe_wepay_enable' => (int)config('v2board.stripe_wepay_enable', 0),
                    'stripe_card_enable' => (int)config('v2board.stripe_card_enable', 0),
                    'stripe_sk_live' => config('v2board.stripe_sk_live'),
                    'stripe_pk_live' => config('v2board.stripe_pk_live'),
                    'stripe_webhook_key' => config('v2board.stripe_webhook_key'),
                    'stripe_currency' => config('v2board.stripe_currency', 'hkd'),
                    // bitpayx
                    'bitpayx_name' => config('v2board.bitpayx_name', '在线支付'),
                    'bitpayx_enable' => (int)config('v2board.bitpayx_enable', 0),
                    'bitpayx_appsecret' => config('v2board.bitpayx_appsecret'),
                    // mGate
                    'mgate_name' => config('v2board.mgate_name', '在线支付'),
                    'mgate_enable' => (int)config('v2board.mgate_enable', 0),
                    'mgate_url' => config('v2board.mgate_url'),
                    'mgate_app_id' => config('v2board.mgate_app_id'),
                    'mgate_app_secret' => config('v2board.mgate_app_secret'),
                    // Epay
                    'epay_name' => config('v2board.epay_name', '在线支付'),
                    'epay_enable' => (int)config('v2board.epay_enable', 0),
                    'epay_url' => config('v2board.epay_url'),
                    'epay_pid' => config('v2board.epay_pid'),
                    'epay_key' => config('v2board.epay_key'),
                ],
                'frontend' => [
                    'frontend_theme_sidebar' => config('v2board.frontend_theme_sidebar', 'light'),
                    'frontend_theme_header' => config('v2board.frontend_theme_header', 'dark'),
                    'frontend_theme_color' => config('v2board.frontend_theme_color', 'default'),
                    'frontend_background_url' => config('v2board.frontend_background_url'),
                    'frontend_admin_path' => config('v2board.frontend_admin_path', 'admin')
                ],
                'server' => [
                    'server_token' => config('v2board.server_token'),
                    'server_license' => config('v2board.server_license'),
                    'server_log_level' => config('v2board.server_log_level', 'none')
                ],
                'tutorial' => [
                    'apple_id' => config('v2board.apple_id')
                ],
                'email' => [
                    'email_template' => config('v2board.email_template', 'default')
                ],
                'telegram' => [
                    'telegram_bot_enable' => config('v2board.telegram_bot_enable', 0),
                    'telegram_bot_token' => config('v2board.telegram_bot_token')
                ]
            ]
        ]);
    }

    public function save(ConfigSave $request)
    {
        $data = $request->input();
        $array = \Config::get('v2board');
        foreach ($data as $k => $v) {
            if (!in_array($k, array_keys($request->validated()))) {
                abort(500, '参数' . $k . '不在规则内，禁止修改');
            }
            $array[$k] = $v;
        }
        $data = var_export($array, 1);
        if (!\File::put(base_path() . '/config/v2board.php', "<?php\n return $data ;")) {
            abort(500, '修改失败');
        }
        \Artisan::call('config:cache');
        if (function_exists('opcache')) {
            opcache_reset();
        }
        return response([
            'data' => true
        ]);
    }
}
