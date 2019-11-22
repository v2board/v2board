<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class TutorialController extends Controller
{
    public function getSubscribeUrl () {
        $user = User::find($request->session()->get('id'));
        return response([
            'data' => config('v2board.app_url', env('APP_URL')) . '/api/v1/client/subscribe?token=' . $user['token']
        ]);
    }

    public function getAppleID () {

    }
}
