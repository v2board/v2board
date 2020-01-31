<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConfigSave extends FormRequest
{
    CONST RULES = [
        'invite.invite_force' => 'in:0,1',
        'invite.invite_commission' => 'integer',
        'invite.invite_gen_limit' => 'integer',
        'invite.invite_never_expire' => 'in:0,1',
        'site.stop_register' => 'in:0,1',
        'site.email_verify' => 'in:0,1',
        'site.app_name' => '',
        'site.app_url' => 'url',
        'site.subscribe_url' => 'url',
        'site.plan_transfer_hour' => 'numeric',
        'site.plan_change_enable' => 'in:0,1',
        'site.try_out_enable' => 'in:0,1',
        'site.try_out_plan_id' => 'integer',
        'site.try_out_hour' => 'numeric',
        // server
        'server.server_token' => 'nullable|min:16',
        'server.server_license' => 'nullable',
        // alipay
        'pay.alipay_enable' => 'in:0,1',
        'pay.alipay_appid' => 'nullable|integer|min:16',
        'pay.alipay_pubkey' => 'max:2048',
        'pay.alipay_privkey' => 'max:2048',
        // stripe
        'pay.stripe_alipay_enable' => 'in:0,1',
        'pay.stripe_wepay_enable' => 'in:0,1',
        'pay.stripe_sk_live' => '',
        'pay.stripe_pk_live' => '',
        'pay.stripe_webhook_key' => '',
        // bitpayx
        'pay.bitpayx_enable' => 'in:0,1',
        'pay.bitpayx_appsecret' => '',
        // paytaro
        'pay.paytaro_enable' => 'in:0,1',
        'pay.paytaro_app_id' => '',
        'pay.paytaro_app_secret' => '',
        // frontend
        'frontend.frontend_theme' => 'in:1,2',
        'frontend.frontend_background_url' => 'nullable|url',
        // tutorial
        'tutorial.apple_id' => 'email',
        'tutorial.apple_id_password' => ''
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
