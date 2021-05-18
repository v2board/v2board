<?php

namespace App\Http\Controllers\Admin\Server;

use App\Http\Requests\Admin\ServerV2raySave;
use App\Http\Requests\Admin\ServerV2rayUpdate;
use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Server;

class V2rayController extends Controller
{
    public function save(ServerV2raySave $request)
    {
        $params = $request->validated();
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

    public function update(ServerV2rayUpdate $request)
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
        $server->show = 0;
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
        $config = $serverService->getV2RayConfig($request->input('node_id'), 23333);
        return response([
            'data' => $config
        ]);
    }
}
