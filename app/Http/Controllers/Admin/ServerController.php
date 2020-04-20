<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ServerSave;
use App\Http\Requests\Admin\ServerSort;
use App\Http\Requests\Admin\ServerUpdate;
use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerGroup;
use App\Models\Server;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServerController extends Controller
{
    public function fetch(Request $request)
    {
        $server = Server::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            if (!empty($server[$i]['tags'])) {
                $server[$i]['tags'] = json_decode($server[$i]['tags']);
            }
            $server[$i]['group_id'] = json_decode($server[$i]['group_id']);
            if ($server[$i]['parent_id']) {
                $server[$i]['last_check_at'] = Cache::get('server_last_check_at_' . $server[$i]['parent_id']);
            } else {
                $server[$i]['last_check_at'] = Cache::get('server_last_check_at_' . $server[$i]['id']);
            }
        }
        return response([
            'data' => $server
        ]);
    }

    public function save(ServerSave $request)
    {
        $params = $request->only(array_keys(ServerSave::RULES));
        $params['group_id'] = json_encode($params['group_id']);
        if (isset($params['tags'])) {
            $params['tags'] = json_encode($params['tags']);
        }

        if (isset($params['dnsSettings'])) {
            if (!is_object(json_decode($params['dnsSettings']))) {
                abort(500, 'DNS规则配置格式不正确');
            }
        }

        if (isset($params['ruleSettings'])) {
            if (!is_object(json_decode($params['ruleSettings']))) {
                abort(500, '审计规则配置格式不正确');
            }
        }

        if (isset($params['networkSettings'])) {
            if (!is_object(json_decode($params['networkSettings']))) {
                abort(500, '传输协议配置格式不正确');
            }
        }

        if (isset($params['tlsSettings'])) {
            if (!is_object(json_decode($params['tlsSettings']))) {
                abort(500, 'TLS配置格式不正确');
            }
        }

        if ($request->input('id')) {
            $server = Server::find($request->input('id'));
            if (!$server) {
                abort(500, '服务器不存在');
            }
            try {
                $server->update($params);
            } catch (\Exception $e) {
                abort(500, '保存失败');
            }
            return response([
                'data' => true
            ]);
        }

        if (!Server::create($params)) {
            abort(500, '创建失败');
        }

        return response([
            'data' => true
        ]);
    }

    public function groupFetch(Request $request)
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

    public function groupSave(Request $request)
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

    public function groupDrop(Request $request)
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

    public function drop(Request $request)
    {
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

    public function update(ServerUpdate $request)
    {
        $params = $request->only([
            'show',
        ]);

        $server = Server::find($request->input('id'));

        if (!$server) {
            abort(500, '该服务器不存在');
        }
        try {
            $server->update($params);
        } catch (\Exception $e) {
            abort(500, '保存失败');
        }

        return response([
            'data' => true
        ]);
    }

    public function copy(Request $request)
    {
        $server = Server::find($request->input('id'));
        if (!$server) {
            abort(500, '服务器不存在');
        }
        if (!Server::create($server->toArray())) {
            abort(500, '复制失败');
        }

        return response([
            'data' => true
        ]);
    }

    public function viewConfig(Request $request)
    {
        $serverService = new ServerService();
        $config = $serverService->getConfig($request->input('node_id'), 23333);
        return response([
            'data' => $config
        ]);
    }

    public function sort(ServerSort $request)
    {
        DB::beginTransaction();
        foreach ($request->input('server_ids') as $k => $v) {
            if (!Server::find($v)->update(['sort' => $k + 1])) {
                DB::rollBack();
                abort(500, '保存失败');
            }
        }
        DB::commit();
        return response([
            'data' => true
        ]);
    }
}
