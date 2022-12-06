<?php

namespace App\Http\Controllers\Admin\Server;

use App\Models\Plan;
use App\Models\ServerShadowsocks;
use App\Models\ServerTrojan;
use App\Models\ServerV2ray;
use App\Models\ServerGroup;
use App\Models\User;
use App\Services\ServerService;
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
        $serverGroups = ServerGroup::get();
        $serverService = new ServerService();
        $servers = $serverService->getAllServers();
        foreach ($serverGroups as $k => $v) {
            $serverGroups[$k]['user_count'] = User::where('group_id', $v['id'])->count();
            $serverGroups[$k]['server_count'] = 0;
            foreach ($servers as $server) {
                if (in_array($v['id'], $server['group_id'])) {
                    $serverGroups[$k]['server_count'] = $serverGroups[$k]['server_count']+1;
                }
            }
        }
        return response([
            'data' => $serverGroups
        ]);
    }

    public function save(Request $request)
    {
        if (empty($request->input('name'))) {
            abort(500, 'Group name cannot be empty');
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
                abort(500, 'Group does not exist');
            }
        }

        $servers = ServerV2ray::all();
        foreach ($servers as $server) {
            if (in_array($request->input('id'), $server->group_id)) {
                abort(500, 'The group is already in use by the node and cannot be deleted');
            }
        }

        if (Plan::where('group_id', $request->input('id'))->first()) {
            abort(500, 'The group is already used by the subscription and cannot be deleted');
        }
        if (User::where('group_id', $request->input('id'))->first()) {
            abort(500, 'The group is already in use by the user and cannot be deleted');
        }
        return response([
            'data' => $serverGroup->delete()
        ]);
    }
}
