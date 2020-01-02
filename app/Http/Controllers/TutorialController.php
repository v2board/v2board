<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tutorial;

class TutorialController extends Controller
{
    public function getSubscribeUrl (Request $request) {
        $user = User::find($request->session()->get('id'));
        return response([
            'data' => [
                'subscribe_url' => config('v2board.subscribe_url', config('v2board.app_url', env('APP_URL'))) . '/api/v1/client/subscribe?token=' . $user['token']
            ]
        ]);
    }

    public function getAppleID (Request $request) {
        $user = User::find($request->session()->get('id'));
        if ($user->expired_at < time()) {
            return response([
                'data' => [
                ]
            ]);
        }
        return response([
            'data' => [
                'apple_id' => config('v2board.apple_id'),
                'apple_id_password' => config('v2board.apple_id_password')
            ]
        ]);
    }

    public function fetch (Request $request) {
        if ($request->input('id')) {
            $tutorial = Tutorial::where('show', 1)
                ->where('id', $request->input('id'))
                ->first();
            if (!$tutorial) {
                abort(500, '教程不存在');
            }
            return response([
                'data' => $tutorial
            ]);
        }
        $tutorial = Tutorial::select(['id', 'title', 'description', 'icon'])
            ->where('show', 1)
            ->get();
        $user = User::find($request->session()->get('id'));
        $response = [
            'data' => [
                'tutorials' => $tutorial,
                'safe_area_var' => [
                    'subscribe_url' => config('v2board.subscribe_url', config('v2board.app_url', env('APP_URL'))) . '/api/v1/client/subscribe?token=' . $user['token'],
                    'app_name' => config('v2board.app_name', 'V2board'),
                    'apple_id' => $user->expired_at > time() ? config('v2board.apple_id', '管理员暂无提供AppleID信息') : '账号过期或未订阅',
                    'apple_id_password' =>  $user->expired_at > time() ? config('v2board.apple_id_password', '管理员暂无提供AppleID信息') : '账号过期或未订阅'
                ]
            ]
        ];
        return response($response);
    }
}
