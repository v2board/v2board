<?php

namespace App\Http\Controllers\Passport;

use Illuminate\Http\Request;
use App\Http\Requests\Passport\LoginIndex;
use App\Http\Controllers\Controller;
use App\Models\User;

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
        $res = [
            'data' => true
        ];
        if ($user->is_admin) {
            $res['is_admin'] = true;
        }
        return response($res);
    }
}
