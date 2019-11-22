<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConfigSave extends FormRequest
{
    public static function filter() {
        return [
            'invite_force',
            'invite_commission',
            'invite_gen_limit',
            'invite_never_expire',
            'stop_register',
            'email_verify',
            'app_name',
            'app_url',
            'server_token',
            // alipay
            'alipay_enable',
            'alipay_appid',
            'alipay_pubkey',
            'alipay_privkey',
            // stripe
            'stripe_sk_live',
            'stripe_pk_live',
            'stripe_alipay_enable',
            'stripe_wepay_enable',
            'stripe_webhook_key',
            // tutorial
            'apple_id',
            'apple_id_password'
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'invite_force' => 'in:0,1',
            'invite_commission' => 'integer',
            'invite_gen_limit' => 'integer',
            'invite_never_expire' => 'in:0,1',
            'stop_register' => 'in:0,1',
            'email_verify' => 'in:0,1',
            'server_token' => 'min:16',
            'app_url' => 'url',
            // alipay
            'alipay_enable' => 'in:0,1',
            'alipay_appid' => 'integer|min:16',
            'alipay_pubkey' => 'max:2048',
            'alipay_privkey' => 'max:2048',
            // stripe
            'stripe_alipay_enable' => 'in:0,1',
            'stripe_wepay_enable' => 'in:0,1',
            // tutorial
            'apple_id' => 'email'
        ];
    }
    
    public function messages()
    {
        return [
        ];
    }
}
