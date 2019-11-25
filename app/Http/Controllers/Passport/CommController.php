<?php

namespace App\Http\Controllers\Passport;

use App\Http\Requests\Passport\CommSendEmailVerify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class CommController extends Controller
{
    public function config () {
        return response([
            'data' => [
                'isEmailVerify' => (int)config('v2board.email_verify', 0) ? 1 : 0,
                'isInviteForce' => (int)config('v2board.invite_force', 0) ? 1 : 0,
            ]
        ]);
    }

    private function isEmailVerify () {
        return response([
            'data' => (int)config('v2board.email_verify', 0) ? 1 : 0
        ]);
    }

    public function sendEmailVerify (CommSendEmailVerify $request) {
        $email = $request->input('email');
        $redisKey = 'sendEmailVerify:' . $email;
        if (Redis::get($redisKey)) {
            abort(500, '验证码已发送，请过一会在请求');
        }
        $code = rand(100000, 999999);
        $subject = config('v2board.app_name', 'V2Board') . '邮箱验证码';
        Mail::send(
            'mail.sendEmailVerify', 
            [
                'code' => $code,
                'name' => config('v2board.app_name', 'V2Board')
            ],
            function ($message) use($email, $subject) { 
                $message->to($email)->subject($subject); 
            }
        );
        if (count(Mail::failures()) >= 1) {
            // 发送失败
            abort(500, '发送失败');
        }

        Redis::set($redisKey, $code);
        Redis::expire($redisKey, 600);
        return response([
            'data' => true
        ]);
    }
}
