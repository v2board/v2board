<?php

return [
    'user' => [
        'changePassword' => [
            'user_not_exist' => 'The user does not exist',
            'old_password_wrong' => 'The old password is wrong',
            'save_failed' => 'Save failed'
        ],
        'info' => [
            'user_not_exist' => 'The user does not exist'
        ],
        'getSubscribe' => [
            'user_not_exist' => 'The user does not exist',
            'plan_not_exist' => 'Subscription plan does not exist',
        ],
        'resetSecurity' => [
            'user_not_exist' => 'The user does not exist',
            'reset_failed' => 'Reset failed'
        ],
        'update' => [
            'user_not_exist' => 'The user does not exist',
            'save_failed' => 'Save failed',
        ],
        'transfer' => [
            'user_not_exist' => 'The user does not exist',
            'params_wrong' => 'Invalid parameter',
            'insufficient_commission_balance' => 'Insufficient commission balance',
            'transfer_failed' => 'Transfer failed'
        ]
    ],
    'ticket' => [
        'fetch' => [
            'ticket_not_exist' => 'Ticket does not exist',
        ],
        'save' => [
            'exist_other_open_ticket' => 'There are other unresolved tickets',
            'ticket_create_failed' => 'Failed to open ticket',
        ],
        'reply' => [
            'params_wrong' => 'Invalid parameter',
            'message_not_empty' => 'Message cannot be empty',
            'ticket_not_exist' => 'Ticket does not exist',
            'ticket_close_not_reply' => 'The ticket is closed and cannot be replied',
            'wait_reply' => 'Please wait for the technical enginneer to reply',
            'ticket_reply_failed' => 'Ticket reply failed',
        ],
        'close' => [
            'params_wrong' => 'Invalid parameter',
            'ticket_not_exist' => 'Ticket does not exist',
            'close_failed' => 'Close failed',
        ],
        'withdraw' => [
            'not_support_withdraw_method' => 'Unsupported withdrawal method',
            'system_require_withdraw_limit' => 'The current required minimum withdrawal commission is: ¥:limitCNY',
            'ticket_subject' => '[Commission Withdrawal Request] This ticket is opened by the system',
            'ticket_create_failed' => 'Failed to open ticket',
            'ticket_message' => "Withdrawal method: :method\r\nPayment account: :account\r\n",
            'not_support_withdraw' => 'Unsupported withdrawal'
        ]
    ],
    'plan' => [
        'fetch' => [
            'plan_not_exist' => 'Subscription plan does not exist'
        ]
    ],
    'order' => [
        'details' => [
            'order_not_exist' => 'Order does not exist',
            'plan_not_exist' => 'Subscription plan does not exist',
        ],
        'save' => [
            'plan_not_exist' => 'Subscription plan does not exist',
            'exist_open_order' => 'You have an unpaid or pending order, please try again later or cancel it',
            'plan_stop_sell' => 'This subscription has been sold out, please choose another subscription',
            'plan_stop_renew' => 'This subscription cannot be renewed, please change to another subscription',
            'plan_stop' => 'This payment cycle cannot be purchased, please choose another cycle',
            'plan_exist_not_buy_package' => 'Subscription has expired or no active subscription, unable to purchase Data Reset Package',
            'plan_expired' => 'This subscription has expired, please change to another subscription',
            'coupon_use_failed' => 'Invalid coupon',
            'insufficient_balance' => 'Insufficient balance',
            'order_create_failed' => 'Failed to create order'
        ],
        'checkout' => [
            'order_not_exist_or_paid' => 'Order does not exist or has been paid',
            'pay_method_not_use' => 'Payment method is not available',
        ],
        'check' => [
            'order_not_exist' => 'Order does not exist'
        ],
        'cancel' => [
            'params_wrong' => 'Invalid parameter',
            'order_not_exist' => 'Order does not exist',
            'only_cancel_pending_order' => 'You can only cancel pending orders',
            'cancel_failed' => 'Cancel failed',
        ],
        'stripeAlipay' => [
            'currency_convert_timeout' => 'Currency conversion has timed out, please try again later',
            'gateway_request_failed' => 'Payment gateway request failed',
        ],
        'stripeWepay' => [
            'currency_convert_timeout' => 'Currency conversion has timed out, please try again later',
            'gateway_request_failed' => 'Payment gateway request failed',
        ],
        'stripeCard' => [
            'currency_convert_timeout' => 'Currency conversion has timed out, please try again later',
            'was_problem' => "Oops, there's a problem... Please refresh the page and try again later",
            'deduction_failed' => 'Payment failed. Please check your credit card information'
        ]
    ],
    'knowledge' => [
        'fetch' => [
            'knowledge_not_exist' => 'Article does not exist',
            'apple_id_must_be_plan' => 'No active subscription. Unable to use our provided Apple ID'
        ],
        'formatAccessData' => [
            'no_access' => 'You must have a valid subscription to view content in this area'
        ]
    ],
    'invite' => [
        'save' => [
            'invite_create_limit' => 'The maximum number of creations has been reached'
        ]
    ],
    'coupon' => [
        'check' => [
            'coupon_not_empty' => 'Coupon cannot be empty',
            'coupon_invalid' => 'Invalid coupon',
            'coupon_not_available_by_number' => 'This coupon is no longer available',
            'coupon_not_available_by_time' => 'This coupon has not yet started',
            'coupon_expired' => 'This coupon has expired',
            'coupon_limit_plan' => 'The coupon code cannot be used for this subscription'
        ]
    ]
];
