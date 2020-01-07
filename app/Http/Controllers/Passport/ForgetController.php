<?php

namespace App\Http\Controllers\Passport;

use App\Http\Requests\Passport\ForgetIndex;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Cache;

class ForgetController extends Controller
{
    public function index (ForgetIndex $request) {
        $redisKey = 'sendEmailVerify:' . $request->input('email');
        if (Cache::get($redisKey) !== $request->input('email_code')) {
            abort(500, '邮箱验证码有误');
        }
        $user = User::where('email', $request->input('email'))->first();
        $user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        if (!$user->save()) {
            abort(500, '重置失败');
        }
        Cache::forget($redisKey);
        return response([
            'data' => true
        ]);
    }
}
