<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class TutorialController extends Controller
{
    public function getSubscribeUrl (Request $request) {
        $user = User::find($request->session()->get('id'));
        return response([
            'data' => [
                'subscribe_url' => config('v2board.app_url', env('APP_URL')) . '/api/v1/client/subscribe?token=' . $user['token']
            ]
        ]);
    }

    public function getAppleID (Request $request) {
        $user = User::find($request->session()->get('id'));
        if ($user->expired_at < time()) {
            return response([
                'data' => false
            ]);
        }
        return response([
            'data' => [
                'apple_id' => config('v2board.apple_id'),
                'apple_id_password' => config('v2board.apple_id_password')
            ]
        ]);
    }
}
