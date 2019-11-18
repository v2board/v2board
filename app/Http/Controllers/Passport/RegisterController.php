<?php

namespace App\Http\Controllers\Passport;

use App\Http\Requests\Passport\RegisterIndex;
use App\Http\Requests\Passport\RegisterSendEmailVerify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redis;
use App\Utils\Helper;
use App\Models\InviteCode;

class RegisterController extends Controller
{
    public function index (RegisterIndex $request) {
        if ((int)config('v2board.stop_register', env('DEFAULT_STOP_REGISTER'))) {
            abort(500, '本站已关闭注册');
        }
        if ((int)config('v2board.invite_force', env('DEFAULT_INVITE_FOCE'))) {
            if (empty($request->input('invite_code'))) {
                abort(500, '必须使用邀请码才可以注册');
            }
        }
        if ((int)config('v2board.email_verify', env('DEFAULT_EMAIL_VERIFY'))) {
            $redisKey = 'sendEmailVerify:' . $request->input('email');
            if (empty($request->input('email_code'))) {
                abort(500, '邮箱验证码不能为空');
            }
            if (Redis::get($redisKey) !== $request->input('email_code')) {
                abort(500, '邮箱验证码有误');
            }
        }
        $email = $request->input('email');
        $password = $request->input('password');
        $exist = User::where('email', $email)->first();
        if ($exist) {
            abort(500, '邮箱已存在系统中');
        }
        $user = new User();
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->last_login_at = time();
        $user->v2ray_uuid = Helper::guid(true);
        $user->token = Helper::guid();
        if ($request->input('invite_code')) {
            $inviteCode = InviteCode::where('code', $request->input('invite_code'))
                ->where('status', 0)
                ->first();
            if (!$inviteCode) {
                if ((int)config('v2board.invite_force', env('DEFAULT_INVITE_FOCE'))) {
                    abort(500, '邀请码无效');
                }
            }
            $user->invite_user_id = $inviteCode->user_id ? $inviteCode->user_id : null;
            if (!(int)config('v2board.invite_never_expire', env('DEFAULT_INVITE_NEVER_EXPIRE'))) {
                $inviteCode->status = 1;
                $inviteCode->save();
            }
        }

        if (!$user->save()) {
            abort(500, '注册失败');
        }
        if ((int)config('v2board.email_verify', env('DEFAULT_EMAIL_VERIFY'))) {
            Redis::del($redisKey);
        }
        return response()->json([
            'data' => true
        ]);
    }
}
