<?php

namespace App\Http\Controllers\Admin\Server;

use App\Models\Plan;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    public function fetch(Request $request)
    {
        if ($request->input('group_id')) {
            return response([
                'data' => [ServerGroup::find($request->input('group_id'))]
            ]);
        }
        return response([
            'data' => ServerGroup::get()
        ]);
    }

    public function save(Request $request)
    {
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

    public function drop(Request $request)
    {
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
}
