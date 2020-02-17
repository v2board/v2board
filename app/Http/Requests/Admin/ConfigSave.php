<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConfigSave extends FormRequest
{
    CONST RULES = [
        'safe_mode_enable' => 'in:0,1',
        'invite_force' => 'in:0,1',
        'invite_commission' => 'integer',
        'invite_gen_limit' => 'integer',
        'invite_never_expire' => 'in:0,1',
        'stop_register' => 'in:0,1',
        'email_verify' => 'in:0,1',
        'app_name' => '',
        'app_description' => '',
        'app_url' => 'url',
        'subscribe_url' => 'url',
        'plan_change_enable' => 'in:0,1',
        'try_out_enable' => 'in:0,1',
        'try_out_plan_id' => 'integer',
        'try_out_hour' => 'numeric',
        'email_whitelist_enable' => 'in:0,1',
        'email_whitelist_suffix' => '',
        // server
        'server_token' => 'nullable|min:16',
        'server_license' => 'nullable',
        // alipay
        'alipay_enable' => 'in:0,1',
        'alipay_appid' => 'nullable|integer|min:16',
        'alipay_pubkey' => 'max:2048',
        'alipay_privkey' => 'max:2048',
        // stripe
        'stripe_alipay_enable' => 'in:0,1',
        'stripe_wepay_enable' => 'in:0,1',
        'stripe_sk_live' => '',
        'stripe_pk_live' => '',
        'stripe_webhook_key' => '',
        // bitpayx
        'bitpayx_enable' => 'in:0,1',
        'bitpayx_appsecret' => '',
        // paytaro
        'paytaro_enable' => 'in:0,1',
        'paytaro_app_id' => '',
        'paytaro_app_secret' => '',
        // frontend
        'frontend_theme_sidebar' => 'in:dark,light',
        'frontend_theme_header' => 'in:dark,light',
        'frontend_theme_color' => 'in:default,darkblue,black',
        'frontend_background_url' => 'nullable|url',
        // tutorial
        'apple_id' => 'email',
        'apple_id_password' => ''
    ];

    public static function filter()
    {
        return array_keys(self::RULES);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return self::RULES;
    }

    public function messages()
    {
        return [
        ];
    }
}
