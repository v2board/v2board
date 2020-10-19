<?php

namespace App\Http\Controllers\Passport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Passport\AuthRegister;
use App\Http\Requests\Passport\AuthForget;
use App\Http\Requests\Passport\AuthLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Plan;
use App\Models\User;
use App\Models\InviteCode;
use App\Utils\Helper;
use App\Utils\Dict;
use App\Utils\CacheKey;
use ReCaptcha\ReCaptcha;

class AuthController extends Controller
{
    public function register(AuthRegister $request)
    {
        if ((int)config('v2board.recaptcha_enable', 0)) {
            $recaptcha = new ReCaptcha(config('v2board.recaptcha_key'));
            $recaptchaResp = $recaptcha->verify($request->input('recaptcha_data'));
            if (!$recaptchaResp->isSuccess()) {
                abort(500, '验证码有误');
            }
        }
        if ((int)config('v2board.email_whitelist_enable', 0)) {
            if (!Helper::emailSuffixVerify(
                $request->input('email'),
                config('v2board.email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT))
            ) {
                abort(500, '邮箱后缀不处于白名单中');
            }
        }
        if ((int)config('v2board.email_gmail_limit_enable', 0)) {
            $prefix = explode('@', $request->input('email'))[0];
            if (strpos($prefix, '.') !== false || strpos($prefix, '+') !== false) {
                abort(500, '不支持Gmail别名邮箱');
            }
        }
        if ((int)config('v2board.stop_register', 0)) {
            abort(500, '本站已关闭注册');
        }
        if ((int)config('v2board.invite_force', 0)) {
            if (empty($request->input('invite_code'))) {
                abort(500, '必须使用邀请码才可以注册');
            }
        }
        if ((int)config('v2board.email_verify', 0)) {
            if (empty($request->input('email_code'))) {
                abort(500, '邮箱验证码不能为空');
            }
            if (Cache::get(CacheKey::get('EMAIL_VERIFY_CODE', $request->input('email'))) !== $request->input('email_code')) {
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
        $user->uuid = Helper::guid(true);
        $user->token = Helper::guid();
        if ($request->input('invite_code')) {
            $inviteCode = InviteCode::where('code', $request->input('invite_code'))
                ->where('status', 0)
                ->first();
            if (!$inviteCode) {
                if ((int)config('v2board.invite_force', 0)) {
                    abort(500, '邀请码无效');
                }
            } else {
                $user->invite_user_id = $inviteCode->user_id ? $inviteCode->user_id : null;
                if (!(int)config('v2board.invite_never_expire', 0)) {
                    $inviteCode->status = 1;
                    $inviteCode->save();
                }
            }
        }

        // try out
        if ((int)config('v2board.try_out_plan_id', 0)) {
            $plan = Plan::find(config('v2board.try_out_plan_id'));
            if ($plan) {
                $user->transfer_enable = $plan->transfer_enable * 1073741824;
                $user->plan_id = $plan->id;
                $user->group_id = $plan->group_id;
                $user->expired_at = time() + (config('v2board.try_out_hour', 1) * 3600);
            }
        }

        if (!$user->save()) {
            abort(500, '注册失败');
        }
        if ((int)config('v2board.email_verify', 0)) {
            Cache::forget(CacheKey::get('EMAIL_VERIFY_CODE', $request->input('email')));
        }
        $request->session()->put('email', $user->email);
        $request->session()->put('id', $user->id);
        return response()->json([
            'data' => true
        ]);
    }

    public function login(AuthLogin $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();
        if (!$user) {
            abort(500, '用户名或密码错误');
        }
        if (!Helper::multiPasswordVerify(
            $user->password_algo,
            $password,
            $user->password)
        ) {
            abort(500, '用户名或密码错误');
        }

        if ($user->banned) {
            abort(500, '该账户已被停止使用');
        }

        $data = [
            'token' => $user->token
        ];
        $request->session()->put('email', $user->email);
        $request->session()->put('id', $user->id);
        if ($user->is_admin) {
            $request->session()->put('is_admin', true);
            $data['is_admin'] = true;
        }
        if ($user->is_staff) {
            $request->session()->put('is_staff', true);
            $data['is_staff'] = true;
        }
        return response([
            'data' => $data
        ]);
    }

    public function token2Login(Request $request)
    {
        if ($request->input('token')) {
            $redirect = '/#/login?verify=' . $request->input('token') . '&redirect=' . ($request->input('redirect') ? $request->input('redirect') : 'dashboard');
            if (config('v2board.app_url')) {
                $location = config('v2board.app_url') . $redirect;
            } else {
                $location = url($redirect);
            }
            return redirect()->to($location)->send();
        }

        if ($request->input('verify')) {
            $key =  CacheKey::get('TEMP_TOKEN', $request->input('verify'));
            $userId = Cache::get($key);
            if (!$userId) {
                abort(500, '令牌有误');
            }
            $user = User::find($userId);
            if (!$user) {
                abort(500, '用户不存在');
            }
            if ($user->banned) {
                abort(500, '该账户已被停止使用');
            }
            $request->session()->put('email', $user->email);
            $request->session()->put('id', $user->id);
            if ($user->is_admin) {
                $request->session()->put('is_admin', true);
            }
            Cache::forget($key);
            return response([
                'data' => true
            ]);
        }
    }

    public function getTempToken(Request $request)
    {
        $user = User::where('token', $request->input('token'))->first();
        if (!$user) {
            abort(500, '令牌有误');
        }

        $code = Helper::guid();
        $key = CacheKey::get('TEMP_TOKEN', $code);
        Cache::put($key, $user->id, 60);
        return response([
            'data' => $code
        ]);
    }

    public function getQuickLoginUrl(Request $request)
    {
        $user = User::where('token', $request->input('token'))->first();
        if (!$user) {
            abort(500, '令牌有误');
        }

        $code = Helper::guid();
        $key = CacheKey::get('TEMP_TOKEN', $code);
        Cache::put($key, $user->id, 60);
        $redirect = '/#/login?verify=' . $code . '&redirect=' . ($request->input('redirect') ? $request->input('redirect') : 'dashboard');
        if (config('v2board.app_url')) {
            $url = config('v2board.app_url') . $redirect;
        } else {
            $url = url($redirect);
        }
        return response([
            'data' => $url
        ]);
    }

    public function check(Request $request)
    {
        $data = [
            'is_login' => $request->session()->get('id') ? true : false
        ];
        if ($request->session()->get('is_admin')) {
            $data['is_admin'] = true;
        }
        return response([
            'data' => $data
        ]);
    }

    public function forget(AuthForget $request)
    {
        if (Cache::get(CacheKey::get('EMAIL_VERIFY_CODE', $request->input('email'))) !== $request->input('email_code')) {
            abort(500, '邮箱验证码有误');
        }
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            abort(500, '该邮箱不存在系统中');
        }
        $user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        $user->password_algo = NULL;
        if (!$user->save()) {
            abort(500, '重置失败');
        }
        Cache::forget(CacheKey::get('EMAIL_VERIFY_CODE', $request->input('email')));
        return response([
            'data' => true
        ]);
    }

}
