<?php

return [
    'auth' => [
        'register' => [
            'verify_incorrect' => 'Invalid code is incorrect',
            'email_suffix_not_in_whitelist' => 'Email suffix is not in the Whitelist',
            'no_support_gmail_alias' => 'Gmail alias is not supported',
            'close_register' => 'Registration has closed',
            'must_use_invite_code' => 'You must use the invitation code to register',
            'email_code_not_empty' => 'Email verification code cannot be empty',
            'email_code_incorrect' => 'Incorrect email verification code',
            'email_exist_system' => 'Email already exists',
            'invalid_invite_code' => 'Invalid invitation code',
            'register_failed' => 'Register failed'
        ],
        'login' => [
            'wrong_email_or_password' => 'Incorrect email or password',
            'account_been_discontinued' => 'Your account has been suspended'
        ],
        'getQuickLoginUrl' => [
            'wrong_token' => 'Token error'
        ],
        'forget' => [
            'email_verification_code_incorrect' => 'Incorrect email verification code',
            'email_not_exist_system' => 'This email is not registered in the system',
            'reset_failed' => 'Reset failed'
        ]
    ],
    'comm' => [
        'sendEmailVerify' => [
            'verification_code_incorrect' => 'Incorrect email verification code',
            'code_sent_request_later' => 'Email verification code has been sent, please request again later',
            'email_verification_code' => 'Email verification code'
        ]
    ]
];
