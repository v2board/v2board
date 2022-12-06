<?php

namespace App\Http\Controllers\Admin\Server;

use App\Http\Requests\Admin\ServerShadowsocksSave;
use App\Http\Requests\Admin\ServerShadowsocksUpdate;
use App\Models\ServerShadowsocks;
use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShadowsocksController extends Controller
{
    public function save(ServerShadowsocksSave $request)
    {
        $params = $request->validated();
        if ($request->input('id')) {
            $server = ServerShadowsocks::find($request->input('id'));
            if (!$server) {
                abort(500, 'Server does not exist');
            }
            try {
                $server->update($params);
            } catch (\Exception $e) {
                abort(500, 'Failed to save');
            }
            return response([
                'data' => true
            ]);
        }

        if (!ServerShadowsocks::create($params)) {
            abort(500, 'Failed to create');
        }

        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        if ($request->input('id')) {
            $server = ServerShadowsocks::find($request->input('id'));
            if (!$server) {
                abort(500, 'Node ID does not exist');
            }
        }
        return response([
            'data' => $server->delete()
        ]);
    }

    public function update(ServerShadowsocksUpdate $request)
    {
        $params = $request->only([
            'show',
        ]);

        $server = ServerShadowsocks::find($request->input('id'));

        if (!$server) {
            abort(500, 'This server does not exist');
        }
        try {
            $server->update($params);
        } catch (\Exception $e) {
            abort(500, 'Failed to update');
        }

        return response([
            'data' => true
        ]);
    }

    public function copy(Request $request)
    {
        $server = ServerShadowsocks::find($request->input('id'));
        $server->show = 0;
        if (!$server) {
            abort(500, 'Server does not exist');
        }
        if (!ServerShadowsocks::create($server->toArray())) {
            abort(500, 'Copy failure');
        }

        return response([
            'data' => true
        ]);
    }
}
