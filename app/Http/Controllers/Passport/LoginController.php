<?php

namespace App\Http\Controllers\Passport;

use Illuminate\Http\Request;
use App\Http\Requests\Passport\LoginIndex;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use App\Utils\Helper;

class LoginController extends Controller
{
    public function index (LoginIndex $request) {
        $email = $request->input('email');
        $password = $request->input('password');
        
        $user = User::where('email', $email)->first();
        if (!$user) {
            abort(500, '用户名或密码错误');
        }
        if (!password_verify($password, $user->password)) {
            abort(500, '用户名或密码错误');
        }
        
        $request->session()->put('email', $user->email);
        $request->session()->put('id', $user->id);
        if ($user->is_admin) {
            $request->session()->put('is_admin', true);
        }
        return response([
            'data' => [
                'is_admin' => $user->is_admin ? 2 : 1,
                'token' => $user->token
            ]
        ]);
    }

    public function token2Login (Request $request) {
        if ($request->input('token')) {
            $user = User::where('token', $request->input('token'))->first();
            if (!$user) {
                return header('Location:' . config('v2board.app_url'));
            }
            $key = 'token2Login_' . Helper::guid();
            Redis::set($key, $user->id);
            Redis::expire($key, 600);
            $redirect = '/#/login?verify='. $key .'&redirect=' . ($request->input('redirect') ? $request->input('redirect') : 'dashboard');
            if (config('v2board.app_url')) {
                $location = config('v2board.app_url') . $redirect;
            } else {
                $location = url($redirect);
            }
            return header('Location:' . $location);
        }

        if ($request->input('verify')) {
            $key = 'token2Login_' . $request->input('verify');
            $userId = Redis::get($key);
            if (!$userId) {
                abort(500, '令牌有误');
            }
            $user = User::find($userId);
            if (!$user) {
                abort(500, '用户不存在');
            }
            $request->session()->put('email', $user->email);
            $request->session()->put('id', $user->id);
            if ($user->is_admin) {
                $request->session()->put('is_admin', true);
            }
            Redis::del($key);
            return response([
                'data' => true
            ]);
        }
    }

    public function check (Request $request) {
        return response([
            'data' => $request->session()->get('id') ? true : false
        ]);
    }
}
