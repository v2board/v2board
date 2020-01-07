<?php

namespace App\Http\Controllers\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    public function __construct(Request $request) {
        $token = $request->input('token');
        if (empty($token)) {
            response()->json([
              'msg' => 'token can not be empty',
            ], 400)->send();
            die();
        }
        if ($token !== config('v2board.server_token')) {
            response()->json([
              'msg' => 'invalid token',
            ], 400)->send();
            die();
        }
    }
}
