<?php

namespace App\Http\Controllers\User;

use App\Utils\Dict;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'isTelegram' => (int)config('v2board.telegram_bot_enable', 0),
                'stripePk' => config('v2board.stripe_pk_live'),
                'withdraw_methods' => config('v2board.commission_withdraw_method', Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT),
                'withdraw_close' => (int)config('v2board.withdraw_close_enable', 0)
            ]
        ]);
    }
}
