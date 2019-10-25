<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class UserController extends Controller
{
    public function index (Request $request) {
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $userModel = User::orderBy('created_at', 'DESC');
        if ($request->input('email')) {
            $userModel->where('email', $request->input('email'));
        }
        $total = $userModel->count();
        return response([
            'data' => $userModel->forPage($current, $pageSize)
                ->get(),
            'total' => $total
        ]);
    }

    public function save (UserSave $request) {
        if ($request->input('id')) {
            $userModel = User::find($request->input('id'));
        } else {
            $userModel = new User();
        }
        if (User::where('email', $request->input('email')->first())) {
            abort(500, '邮箱已被使用');
        }
        $userModel->email = $request->input('email');
        $userModel->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        $userModel->transfer_enable = $request->input('transfer_enable') * 1073741824;
        $userModel->expired_at = $request->input('expired_at');
        $userModel->banned = $request->input('banned');
        $userModel->is_admin = $request->input('is_admin');
        if (!$userModel->save()) {
            abort(500, '保存失败');
        }
        return response([
            'data' => true
        ]);
    }
}
