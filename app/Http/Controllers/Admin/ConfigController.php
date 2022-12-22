<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ConfigSave;
use App\Jobs\SendEmailJob;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Utils\Dict;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

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

    public function getThemeTemplate()
    {
        $path = public_path('theme/');
        $files = array_map(function ($item) use ($path) {
            return str_replace($path, '', $item);
        }, glob($path . '*'));
        return response([
            'data' => $files
        ]);
    }

    public function testSendMail(Request $request)
    {
        $obj = new SendEmailJob([
            'email' => $request->user['email'],
            'subject' => 'This is v2board test email',
            'template_name' => 'notify',
            'template_value' => [
                'name' => config('v2board.app_name', 'V2Board'),
                'content' => 'This is v2board test email',
                'url' => config('v2board.app_url')
            ]
        ]);
        return response([
            'data' => true,
            'log' => $obj->handle()
        ]);
    }

    public function setTelegramWebhook(Request $request)
    {
        $hookUrl = url('/api/v1/guest/telegram/webhook?access_token=' . md5(config('v2board.telegram_bot_token', $request->input('telegram_bot_token'))));
        $telegramService = new TelegramService($request->input('telegram_bot_token'));
        $telegramService->getMe();
        $telegramService->setWebhook($hookUrl);
        return response([
            'data' => true
        ]);
    }

    public function fetch(Request $request)
    {
        $key = $request->input('key');
        $data = [
            'invite' => [
                'invite_force' => (int)config('v2board.invite_force', 0),
                'invite_commission' => config('v2board.invite_commission', 10),
                'invite_gen_limit' => config('v2board.invite_gen_limit', 5),
                'invite_never_expire' => config('v2board.invite_never_expire', 0),
                'commission_first_time_enable' => config('v2board.commission_first_time_enable', 1),
                'commission_auto_check_enable' => config('v2board.commission_auto_check_enable', 1),
                'commission_withdraw_limit' => config('v2board.commission_withdraw_limit', 100),
                'commission_withdraw_method' => config('v2board.commission_withdraw_method', Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT),
                'withdraw_close_enable' => config('v2board.withdraw_close_enable', 0),
                'commission_distribution_enable' => config('v2board.commission_distribution_enable', 0),
                'commission_distribution_l1' => config('v2board.commission_distribution_l1'),
                'commission_distribution_l2' => config('v2board.commission_distribution_l2'),
                'commission_distribution_l3' => config('v2board.commission_distribution_l3')
            ],
            'site' => [
                'logo' => config('v2board.logo'),
                'force_https' => (int)config('v2board.force_https', 0),
                'stop_register' => (int)config('v2board.stop_register', 0),
                'app_name' => config('v2board.app_name', 'V2Board'),
                'app_description' => config('v2board.app_description', 'V2Board is best!'),
                'app_url' => config('v2board.app_url'),
                'subscribe_url' => config('v2board.subscribe_url'),
                'try_out_plan_id' => (int)config('v2board.try_out_plan_id', 0),
                'try_out_hour' => (int)config('v2board.try_out_hour', 1),
                'tos_url' => config('v2board.tos_url'),
                'currency' => config('v2board.currency', 'CNY'),
                'currency_symbol' => config('v2board.currency_symbol', '¥'),
            ],
            'subscribe' => [
                'plan_change_enable' => (int)config('v2board.plan_change_enable', 1),
                'reset_traffic_method' => (int)config('v2board.reset_traffic_method', 0),
                'surplus_enable' => (int)config('v2board.surplus_enable', 1),
                'new_order_event_id' => (int)config('v2board.new_order_event_id', 0),
                'renew_order_event_id' => (int)config('v2board.renew_order_event_id', 0),
                'change_order_event_id' => (int)config('v2board.change_order_event_id', 0),
                'show_info_to_server_enable' => (int)config('v2board.show_info_to_server_enable', 0)
            ],
            'frontend' => [
                'frontend_theme' => config('v2board.frontend_theme', 'v2board'),
                'frontend_theme_sidebar' => config('v2board.frontend_theme_sidebar', 'light'),
                'frontend_theme_header' => config('v2board.frontend_theme_header', 'dark'),
                'frontend_theme_color' => config('v2board.frontend_theme_color', 'default'),
                'frontend_background_url' => config('v2board.frontend_background_url'),
            ],
            'server' => [
                'server_token' => config('v2board.server_token'),
                'server_pull_interval' => config('v2board.server_pull_interval', 60),
                'server_push_interval' => config('v2board.server_push_interval', 60),
            ],
            'email' => [
                'email_template' => config('v2board.email_template', 'default'),
                'email_host' => config('v2board.email_host'),
                'email_port' => config('v2board.email_port'),
                'email_username' => config('v2board.email_username'),
                'email_password' => config('v2board.email_password'),
                'email_encryption' => config('v2board.email_encryption'),
                'email_from_address' => config('v2board.email_from_address')
            ],
            'telegram' => [
                'telegram_bot_enable' => config('v2board.telegram_bot_enable', 0),
                'telegram_bot_token' => config('v2board.telegram_bot_token'),
                'telegram_discuss_link' => config('v2board.telegram_discuss_link')
            ],
            'app' => [
                'windows_version' => config('v2board.windows_version'),
                'windows_download_url' => config('v2board.windows_download_url'),
                'macos_version' => config('v2board.macos_version'),
                'macos_download_url' => config('v2board.macos_download_url'),
                'android_version' => config('v2board.android_version'),
                'android_download_url' => config('v2board.android_download_url')
            ],
            'safe' => [
                'email_verify' => (int)config('v2board.email_verify', 0),
                'safe_mode_enable' => (int)config('v2board.safe_mode_enable', 0),
                'secure_path' => config('v2board.secure_path', config('v2board.frontend_admin_path', hash('crc32b', config('app.key')))),
                'email_whitelist_enable' => (int)config('v2board.email_whitelist_enable', 0),
                'email_whitelist_suffix' => config('v2board.email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT),
                'email_gmail_limit_enable' => config('v2board.email_gmail_limit_enable', 0),
                'recaptcha_enable' => (int)config('v2board.recaptcha_enable', 0),
                'recaptcha_key' => config('v2board.recaptcha_key'),
                'recaptcha_site_key' => config('v2board.recaptcha_site_key'),
                'register_limit_by_ip_enable' => (int)config('v2board.register_limit_by_ip_enable', 0),
                'register_limit_count' => config('v2board.register_limit_count', 3),
                'register_limit_expire' => config('v2board.register_limit_expire', 60),
                'password_limit_enable' => (int)config('v2board.password_limit_enable', 1),
                'password_limit_count' => config('v2board.password_limit_count', 5),
                'password_limit_expire' => config('v2board.password_limit_expire', 60)
            ]
        ];
        if ($key && isset($data[$key])) {
            return response([
                'data' => [
                    $key => $data[$key]
                ]
            ]);
        };
        // TODO: default should be in Dict
        return response([
            'data' => $data
        ]);
    }

    public function save(ConfigSave $request)
    {
        $data = $request->validated();
        $config = config('v2board');
        foreach (ConfigSave::RULES as $k => $v) {
            if (!in_array($k, array_keys(ConfigSave::RULES))) {
                unset($config[$k]);
                continue;
            }
            if (array_key_exists($k, $data)) {
                $config[$k] = $data[$k];
            }
        }
        $data = var_export($config, 1);
        if (!File::put(base_path() . '/config/v2board.php', "<?php\n return $data ;")) {
            abort(500, '修改失败');
        }
        if (function_exists('opcache_reset')) {
            if (opcache_reset() === false) {
                abort(500, '缓存清除失败，请卸载或检查opcache配置状态');
            }
        }
        Artisan::call('config:cache');
        return response([
            'data' => true
        ]);
    }
}
