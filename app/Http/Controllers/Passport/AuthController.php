<?php

namespace App\Http\Controllers\Passport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Passport\AuthRegister;
use App\Http\Requests\Passport\AuthForget;
use App\Http\Requests\Passport\AuthLogin;
use App\Jobs\SendEmailJob;
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
    public function loginWithMailLink(Request $request)
    {
        if (!(int)config('v2board.login_with_mail_link_enable')) {
            abort(404);
        }
        $params = $request->validate([
            'email' => 'required|email',
            'redirect' => 'nullable'
        ]);

        if (Cache::get(CacheKey::get('LAST_SEND_LOGIN_WITH_MAIL_LINK_TIMESTAMP', $params['email']))) {
            abort(500, __('Sending frequently, please try again later'));
        }

        $user = User::where('email', $params['email'])->first();
        if (!$user) {
            return response([
                'data' => true
            ]);
        }

        $code = Helper::guid();
        $key = CacheKey::get('TEMP_TOKEN', $code);
        Cache::put($key, $user->id, 300);
        Cache::put(CacheKey::get('LAST_SEND_LOGIN_WITH_MAIL_LINK_TIMESTAMP', $params['email']), time(), 60);


        $redirect = '/#/login?verify=' . $code . '&redirect=' . ($request->input('redirect') ? $request->input('redirect') : 'dashboard');
        if (config('v2board.app_url')) {
            $link = config('v2board.app_url') . $redirect;
        } else {
            $link = url($redirect);
        }

        SendEmailJob::dispatch([
            'email' => $user->email,
            'subject' => __('Login to :name', [
                'name' => config('v2board.app_name', 'V2Board')
            ]),
            'template_name' => 'login',
            'template_value' => [
                'name' => config('v2board.app_name', 'V2Board'),
                'link' => $link,
                'url' => config('v2board.app_url')
            ]
        ]);

        return response([
            'data' => $link
        ]);

    }

    public function register(AuthRegister $request)
    {
        if ((int)config('v2board.register_limit_by_ip_enable', 0)) {
            $registerCountByIP = Cache::get(CacheKey::get('REGISTER_IP_RATE_LIMIT', $request->ip())) ?? 0;
            if ((int)$registerCountByIP >= (int)config('v2board.register_limit_count', 3)) {
                abort(500, __('Register frequently, please try again after 1 hour'));
            }
        }
        if ((int)config('v2board.recaptcha_enable', 0)) {
            $recaptcha = new ReCaptcha(config('v2board.recaptcha_key'));
            $recaptchaResp = $recaptcha->verify($request->input('recaptcha_data'));
            if (!$recaptchaResp->isSuccess()) {
                abort(500, __('Invalid code is incorrect'));
            }
        }
        if ((int)config('v2board.email_whitelist_enable', 0)) {
            if (!Helper::emailSuffixVerify(
                $request->input('email'),
                config('v2board.email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT))
            ) {
                abort(500, __('Email suffix is not in the Whitelist'));
            }
        }
        if ((int)config('v2board.email_gmail_limit_enable', 0)) {
            $prefix = explode('@', $request->input('email'))[0];
            if (strpos($prefix, '.') !== false || strpos($prefix, '+') !== false) {
                abort(500, __('Gmail alias is not supported'));
            }
        }
        if ((int)config('v2board.stop_register', 0)) {
            abort(500, __('Registration has closed'));
        }
        if ((int)config('v2board.invite_force', 0)) {
            if (empty($request->input('invite_code'))) {
                abort(500, __('You must use the invitation code to register'));
            }
        }
        if ((int)config('v2board.email_verify', 0)) {
            if (empty($request->input('email_code'))) {
                abort(500, __('Email verification code cannot be empty'));
            }
            if (Cache::get(CacheKey::get('EMAIL_VERIFY_CODE', $request->input('email'))) !== $request->input('email_code')) {
                abort(500, __('Incorrect email verification code'));
            }
        }
        $email = $request->input('email');
        $password = $request->input('password');
        $exist = User::where('email', $email)->first();
        if ($exist) {
            abort(500, __('Email already exists'));
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
                    abort(500, __('Invalid invitation code'));
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
            abort(500, __('Register failed'));
        }
        if ((int)config('v2board.email_verify', 0)) {
            Cache::forget(CacheKey::get('EMAIL_VERIFY_CODE', $request->input('email')));
        }

        $data = [
            'token' => $user->token,
            'auth_data' => base64_encode("{$user->email}:{$user->password}")
        ];

        $user->last_login_at = time();
        $user->save();

        if ((int)config('v2board.register_limit_by_ip_enable', 0)) {
            Cache::put(
                CacheKey::get('REGISTER_IP_RATE_LIMIT', $request->ip()),
                (int)$registerCountByIP + 1,
                (int)config('v2board.register_limit_expire', 60) * 60
            );
        }
        return response()->json([
            'data' => $data
        ]);
    }

    public function login(AuthLogin $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();
        if (!$user) {
            abort(500, __('Incorrect email or password'));
        }
        if (!Helper::multiPasswordVerify(
            $user->password_algo,
            $user->password_salt,
            $password,
            $user->password)
        ) {
            abort(500, __('Incorrect email or password'));
        }

        if ($user->banned) {
            abort(500, __('Your account has been suspended'));
        }

        $data = [
            'token' => $user->token,
            'auth_data' => base64_encode("{$user->email}:{$user->password}")
        ];

        if ($user->is_admin) $data['is_admin'] = true;
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
                abort(500, __('Token error'));
            }
            $user = User::find($userId);
            if (!$user) {
                abort(500, __('The user does not '));
            }
            if ($user->banned) {
                abort(500, __('Your account has been suspended'));
            }
            $data = [
                'token' => $user->token,
                'auth_data' => base64_encode("{$user->email}:{$user->password}")
            ];
            Cache::forget($key);
            return response([
                'data' => $data
            ]);
        }
    }

    public function getTempToken(Request $request)
    {
        $user = User::where('token', $request->input('token'))->first();
        if (!$user) {
            abort(500, __('Token error'));
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
        $authorization = $request->input('auth_data') ?? $request->header('authorization');
        if (!$authorization) abort(403, '未登录或登陆已过期');

        $authData = explode(':', base64_decode($authorization));
        if (!isset($authData[0]) || !isset($authData[1])) abort(403, __('Token error'));
        $user = User::where('email', $authData[0])
            ->where('password', $authData[1])
            ->first();
        if (!$user) {
            abort(500, __('Token error'));
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

    public function forget(AuthForget $request)
    {
        if (Cache::get(CacheKey::get('EMAIL_VERIFY_CODE', $request->input('email'))) !== $request->input('email_code')) {
            abort(500, __('Incorrect email verification code'));
        }
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            abort(500, __('This email is not registered in the system'));
        }
        $user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        $user->password_algo = NULL;
        $user->password_salt = NULL;
        if (!$user->save()) {
            abort(500, __('Reset failed'));
        }
        Cache::forget(CacheKey::get('EMAIL_VERIFY_CODE', $request->input('email')));
        return response([
            'data' => true
        ]);
    }
}
