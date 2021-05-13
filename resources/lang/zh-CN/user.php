<?php

return [
    'user' => [
        'changePassword' => [
            'user_not_exist' => '该用户不存在',
            'old_password_wrong' => '旧密码有误',
            'save_failed' => '保存失败'
        ],
        'info' => [
            'user_not_exist' => '该用户不存在'
        ],
        'getSubscribe' => [
            'user_not_exist' => '该用户不存在',
            'plan_not_exist' => '订阅计划不存在',
        ],
        'resetSecurity' => [
            'user_not_exist' => '该用户不存在',
            'reset_failed' => '重置失败'
        ],
        'update' => [
            'user_not_exist' => '该用户不存在',
            'save_failed' => '保存失败',
        ],
        'transfer' => [
            'user_not_exist' => '该用户不存在',
            'params_wrong' => '参数错误',
            'insufficient_commission_balance' => '推广佣金余额不足',
            'transfer_failed' => '划转失败'
        ]
    ],
    'ticket' => [
        'fetch' => [
            'ticket_not_exist' => '工单不存在',
        ],
        'save' => [
            'exist_other_open_ticket' => '存在其它工单尚未处理',
            'ticket_create_failed' => '工单创建失败',
        ],
        'reply' => [
            'params_wrong' => '参数错误',
            'message_not_empty' => '消息不能为空',
            'ticket_not_exist' => '工单不存在',
            'ticket_close_not_reply' => '工单已关闭，无法回复',
            'wait_reply' => '请等待技术支持回复',
            'ticket_reply_failed' => '工单回复失败',
        ],
        'close' => [
            'params_wrong' => '参数错误',
            'ticket_not_exist' => '工单不存在',
            'close_failed' => '关闭失败',
        ],
        'withdraw' => [
            'not_support_withdraw_method' => '不支持的提现方式',
            'system_require_withdraw_limit' => '当前系统要求的最少提现佣金为：¥:limitCNY',
            'ticket_subject' => '[提现申请] 本工单由系统发出',
            'ticket_create_failed' => '工单创建失败',
            'ticket_message' => "提现方式：:method\r\n提现账号：:account\r\n",
            'not_support_withdraw' => '不支持提现'
        ]
    ],
    'plan' => [
        'fetch' => [
            'plan_not_exist' => '订阅计划不存在'
        ]
    ],
    'order' => [
        'details' => [
            'order_not_exist' => '订单不存在',
            'plan_not_exist' => '订阅计划不存在',
        ],
        'save' => [
            'plan_not_exist' => '订阅计划不存在',
            'exist_open_order' => '您有未付款或开通中的订单，请稍后再试或将其取消',
            'plan_stop_sell' => '该订阅已售罄，请更换其它订阅',
            'plan_stop_renew' => '该订阅无法续费，请更换其它订阅',
            'plan_stop' => '该订阅周期无法进行购买，请选择其它周期',
            'plan_exist_not_buy_package' => '订阅已过期或无有效订阅，无法购买重置包',
            'plan_expired' => '订阅已过期，请更换其它订阅',
            'coupon_use_failed' => '优惠券使用失败',
            'insufficient_balance' => '余额不足',
            'order_create_failed' => '订单创建失败'
        ],
        'checkout' => [
            'order_not_exist_or_paid' => '订单不存在或已支付',
            'pay_method_not_use' => '支付方式不可用',
        ],
        'check' => [
            'order_not_exist' => '订单不存在'
        ],
        'cancel' => [
            'params_wrong' => '参数有误',
            'order_not_exist' => '订单不存在',
            'only_cancel_pending_order' => '只可以取消待支付订单',
            'cancel_failed' => '取消失败',
        ],
        'stripeAlipay' => [
            'currency_convert_timeout' => '货币转换超时，请稍后再试',
            'gateway_request_failed' => '支付网关请求失败',
        ],
        'stripeWepay' => [
            'currency_convert_timeout' => '货币转换超时，请稍后再试',
            'gateway_request_failed' => '支付网关请求失败',
        ],
        'stripeCard' => [
            'currency_convert_timeout' => '货币转换超时，请稍后再试',
            'was_problem' => '遇到了点问题，请刷新页面稍后再试',
            'deduction_failed' => '扣款失败，请检查信用卡信息'
        ]
    ],
    'knowledge' => [
        'fetch' => [
            'knowledge_not_exist' => '文章不存在',
            'apple_id_must_be_plan' => '无有效订阅，无法使用本站提供的 AppleID'
        ],
        'formatAccessData' => [
            'no_access' => '你必须拥有有效的订阅才可以查看该区域的内容'
        ]
    ],
    'invite' => [
        'save' => [
            'invite_create_limit' => '已达到创建数量上限'
        ]
    ],
    'coupon' => [
        'check' => [
            'coupon_not_empty' => '优惠券不能为空',
            'coupon_invalid' => '优惠券无效',
            'coupon_not_available_by_number' => '优惠券已无可用次数',
            'coupon_not_available_by_time' => '优惠券还未到可用时间',
            'coupon_expired' => '优惠券已过期',
            'coupon_limit_plan' => '该订阅无法使用此优惠码'
        ]
    ]
];
