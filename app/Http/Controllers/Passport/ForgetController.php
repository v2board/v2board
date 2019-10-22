<?php

namespace App\Http\Controllers\Passport;

use App\Http\Requests\Passport\ForgetIndex;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class ForgetController extends Controller
{
    public function index (ForgetIndex $request) {
        $user = User::where('email', $request->input('email'))->first();
        $redisKey = 'sendEmailVerify:' . $request->input('email');
        if (Redis::get($redisKey) !== $request->input('email_code')) {
            abort(500, '邮箱验证码有误');
        }
        $user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        if (!$user->save()) {
            abort(500, '重置失败');
        }
        Redis::del($redisKey);
        return response([
            'data' => true
        ]);
    }
}
