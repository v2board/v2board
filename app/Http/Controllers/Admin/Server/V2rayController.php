<?php

namespace App\Http\Controllers\Admin\Server;

use App\Http\Requests\Admin\ServerV2raySave;
use App\Http\Requests\Admin\ServerV2rayUpdate;
use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerV2ray;

class V2rayController extends Controller
{
    public function save(ServerV2raySave $request)
    {
        $params = $request->validated();

        if ($request->input('id')) {
            $server = ServerV2ray::find($request->input('id'));
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

        if (!ServerV2ray::create($params)) {
            abort(500, 'Failed to create');
        }

        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        if ($request->input('id')) {
            $server = ServerV2ray::find($request->input('id'));
            if (!$server) {
                abort(500, 'Node ID does not exist');
            }
        }
        return response([
            'data' => $server->delete()
        ]);
    }

    public function update(ServerV2rayUpdate $request)
    {
        $params = $request->only([
            'show',
        ]);

        $server = ServerV2ray::find($request->input('id'));

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
        $server = ServerV2ray::find($request->input('id'));
        $server->show = 0;
        if (!$server) {
            abort(500, 'Server does not exist');
        }
        if (!ServerV2ray::create($server->toArray())) {
            abort(500, 'Copy failure');
        }

        return response([
            'data' => true
        ]);
    }
}
