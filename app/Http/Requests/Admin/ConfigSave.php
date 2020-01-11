<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConfigSave extends FormRequest
{
    CONST RULES = [
        'invite_force' => 'in:0,1',
        'invite_commission' => 'integer',
        'invite_gen_limit' => 'integer',
        'invite_never_expire' => 'in:0,1',
        'stop_register' => 'in:0,1',
        'email_verify' => 'in:0,1',
        'app_name' => '',
        'app_url' => 'url',
        'subscribe_url' => 'url',
        'plan_transfer_hour' => 'numeric',
        'plan_change_enable' => 'in:0,1',
        'try_out_enable' => 'in:0,1',
        'try_out_plan_id' => 'integer',
        'try_out_hour' => 'numeric',
        // server
        'server_token' => 'nullable|min:16',
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
        // tutorial
        'apple_id' => 'email',
        'apple_id_password' => ''
    ];

    public static function filter()
    {
        // abort(500, json_encode(array_keys(self::RULES)));
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
