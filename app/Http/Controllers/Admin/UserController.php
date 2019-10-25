<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserUpdate;
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

    public function update (UserUpdate $request) {
    	$fetchData = $request->only([
    		'email',
    		'password',
    		'transfer_enable',
    		'expired_at',
    		'banned',
    		'is_admin'
		]);
        $user = User::find($request->input('id'));
        if (!$user) {
            abort(500, '用户不存在');
        }
        if (User::where('email', $fetchData['email'])->first() && $user->email !== $fetchData['email']) {
            abort(500, '邮箱已被使用');
        }
        if ($fetchData['password']) {
        	$fetchData['password'] = password_hash($fetchData['password'], PASSWORD_DEFAULT);
        } else {
        	unset($fetchData['password']);
        }
        $fetchData['transfer_enable'] = $fetchData['transfer_enable'] * 1073741824;
        if (!$user->update($fetchData)) {
            abort(500, '保存失败');
        }
        return response([
            'data' => true
        ]);
    }
}
