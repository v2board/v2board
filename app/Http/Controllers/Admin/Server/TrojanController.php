<?php

namespace App\Http\Controllers\Admin\Server;

use App\Http\Requests\Admin\ServerTrojanSave;
use App\Http\Requests\Admin\ServerTrojanUpdate;
use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerTrojan;

class TrojanController extends Controller
{
    public function save(ServerTrojanSave $request)
    {
        $params = $request->validated();
        if ($request->input('id')) {
            $server = ServerTrojan::find($request->input('id'));
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

        if (!ServerTrojan::create($params)) {
            abort(500, 'Failed to create');
        }

        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        if ($request->input('id')) {
            $server = ServerTrojan::find($request->input('id'));
            if (!$server) {
                abort(500, 'Node ID does not exist');
            }
        }
        return response([
            'data' => $server->delete()
        ]);
    }

    public function update(ServerTrojanUpdate $request)
    {
        $params = $request->only([
            'show',
        ]);

        $server = ServerTrojan::find($request->input('id'));

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
        $server = ServerTrojan::find($request->input('id'));
        $server->show = 0;
        if (!$server) {
            abort(500, 'Server does not exist');
        }
        if (!ServerTrojan::create($server->toArray())) {
            abort(500, 'Copy failure');
        }

        return response([
            'data' => true
        ]);
    }
    public function viewConfig(Request $request)
    {
        $serverService = new ServerService();
        $config = $serverService->getTrojanConfig($request->input('node_id'), 23333);
        return response([
            'data' => $config
        ]);
    }
}
