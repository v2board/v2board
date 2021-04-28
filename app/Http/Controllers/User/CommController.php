<?php

namespace App\Http\Controllers\User;

use App\Models\Payment;
use App\Utils\Dict;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'is_telegram' => (int)config('v2board.telegram_bot_enable', 0),
                'stripe_pk' => config('v2board.stripe_pk_live'),
                'withdraw_methods' => config('v2board.commission_withdraw_method', Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT),
                'withdraw_close' => (int)config('v2board.withdraw_close_enable', 0)
            ]
        ]);
    }

    public function getStripePublicKey(Request $request)
    {
        $payment = Payment::where('id', $request->input('id'))
            ->where('payment', 'StripeCredit')
            ->first();
        if (!$payment) abort(500, 'payment is not found');
        $config = json_decode($payment->config, true);
        return response([
            'data' => $config['stripe_pk_live']
        ]);
    }
}
