<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'isTelegram' => (int)config('v2board.telegram_bot_enable', 0),
                'stripePk' => config('v2board.stripe_pk_live')
            ]
        ]);
    }
}
