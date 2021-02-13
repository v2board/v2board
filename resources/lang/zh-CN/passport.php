<?php

return [
    'auth' => [
        'register' => [
            'verify_incorrect' => '验证码有误',
            'email_suffix_not_in_whitelist' => '邮箱后缀不处于白名单中',
            'no_support_gmail_alias' => '不支持 Gmail 别名邮箱',
            'close_register' => '本站已关闭注册',
            'must_use_invite_code' => '必须使用邀请码才可以注册',
            'email_code_not_empty' => '邮箱验证码不能为空',
            'email_code_incorrect' => '邮箱验证码有误',
            'email_exist_system' => '邮箱已存在系统中',
            'invalid_invite_code' => '邀请码无效',
            'register_failed' => '注册失败'
        ],
        'login' => [
            'wrong_email_or_password' => '邮箱或密码错误',
            'account_been_discontinued' => '该账户已被停止使用'
        ],
        'getQuickLoginUrl' => [
            'wrong_token' => '令牌有误'
        ],
        'forget' => [
            'email_verification_code_incorrect' => '邮箱验证码有误',
            'email_not_exist_system' => '该邮箱不存在系统中',
            'reset_failed' => '重置失败'
        ]
    ],
    'comm' => [
        'sendEmailVerify' => [
            'verification_code_incorrect' => '验证码有误',
            'code_sent_request_later' => '验证码已发送，请过一会再请求',
            'email_verification_code' => '邮箱验证码'
        ]
    ]
];
