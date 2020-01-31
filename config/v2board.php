<?php
return [
    'invite' => [
        'invite_force' => 0,
        'invite_commission' => 10,
        'invite_gen_limit' => 5,
        'invite_never_expire' => 0
    ],
    'site' => [
        'stop_register' => 0,
        'email_verify' => 0,
        'app_name' => 'V2Board',
        'app_url' => '',
        'subscribe_url' => '',
        'plan_change_enable' => 1,
        'plan_transfer_hour' => 12,
        'try_out_enable' => 0,
        'try_out_plan_id' => '',
        'try_out_hour' => 1
    ],
    'pay' => [
        // alipay
        'alipay_enable' => '',
        'alipay_appid' => '',
        'alipay_pubkey' => '',
        'alipay_privkey' => '',
        // stripe
        'stripe_sk_live' => '',
        'stripe_pk_live' => '',
        'stripe_alipay_enable' => 0,
        'stripe_wepay_enable' => 0,
        'stripe_webhook_key' => '',
        // bitpayx
        'bitpayx_enable' => '',
        'bitpayx_appsecret' => '',
        // paytaro
        'paytaro_enable' => 0,
        'paytaro_app_id' => '',
        'paytaro_app_secret' => ''
    ],
    'frontend' => [
        'frontend_theme' => 1,
        'frontend_background_url' => ''
    ],
    'server' => [
        'server_token' => '',
        'server_license' => ''
    ],
    'tutorial' => [
        'apple_id' => ''
    ]
];
