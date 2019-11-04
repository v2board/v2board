<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ServerSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerGroup;
use App\Models\Server;
use App\Models\Plan;
use App\Models\User;

class ServerController extends Controller
{
    public function index (Request $request) {
        return response([
            'data' => Server::get()
        ]);
    }
    
    public function save (ServerSave $request) {
        if ($request->input('id')) {
            $server = Server::find($request->input('id'));
            if (!$server) {
                abort(500, '服务器不存在');
            }
        } else {
            $server = new Server();
        }
        $server->group_id = json_encode($request->input('group_id'));
        $server->name = $request->input('name');
        $server->host = $request->input('host');
        $server->port = $request->input('port');
        $server->server_port = $request->input('server_port');
        $server->tls = $request->input('tls');
        $server->tags = $request->input('tags') ? json_encode($request->input('tags')) : NULL;
        $server->rate = $request->input('rate');
        return response([
            'data' => $server->save()
        ]);
    }
    
    public function group (Request $request) {
        if ($request->input('group_id')) {
            return response([
                'data' => [ServerGroup::find($request->input('group_id'))]
            ]);
        }
        return response([
            'data' => ServerGroup::get()
        ]);
    }

    public function groupSave (Request $request) {
        if (empty($request->input('name'))) {
            abort(500, '组名不能为空');
        }
        
        if ($request->input('id')) {
            $serverGroup = ServerGroup::find($request->input('id'));
        } else {
            $serverGroup = new ServerGroup();
        }

        $serverGroup->name = $request->input('name');
        return response([
            'data' => $serverGroup->save()
        ]);
    }

    public function groupDrop (Request $request) {
        if ($request->input('id')) {
            $serverGroup = ServerGroup::find($request->input('id'));
            if (!$serverGroup) {
                abort(500, '组不存在');
            }
        }

        $servers = Server::all();
        foreach ($servers as $server) {
            $groupId = json_decode($server->group_id);
            if (in_array($request->input('id'), $groupId)) {
                abort(500, '该组已被节点所使用，无法删除');
            }
        }

        if (Plan::where('group_id', $request->input('id'))->first()) {
            abort(500, '该组已被订阅所使用，无法删除');
        }
        if (User::where('group_id', $request->input('id'))->first()) {
            abort(500, '该组已被用户所使用，无法删除');
        }
        return response([
            'data' => $serverGroup->delete()
        ]);
    }
    
    public function drop (Request $request) {
        if ($request->input('id')) {
            $server = Server::find($request->input('id'));
            if (!$server) {
                abort(500, '节点ID不存在');
            }
        }
        return response([
            'data' => $server->delete()
        ]);
    }
}
