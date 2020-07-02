<?php

namespace App\Http\Controllers\Admin\Server;

use App\Http\Requests\Admin\ServerTrojanSave;
use App\Http\Requests\Admin\ServerTrojanSort;
use App\Http\Requests\Admin\ServerTrojanUpdate;
use App\Services\ServerService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerTrojan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrojanController extends Controller
{
    public function fetch(Request $request)
    {
        $server = ServerTrojan::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            if (!empty($server[$i]['tags'])) {
                $server[$i]['tags'] = json_decode($server[$i]['tags']);
            }
            $server[$i]['group_id'] = json_decode($server[$i]['group_id']);
            $server[$i]['online'] = Cache::get(CacheKey::get('SERVER_TROJAN_ONLINE_USER', $server[$i]['id']));
            if ($server[$i]['parent_id']) {
                $server[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $server[$i]['parent_id']));
            } else {
                $server[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $server[$i]['id']));
            }
        }
        return response([
            'data' => $server
        ]);
    }

    public function save(ServerTrojanSave $request)
    {
        $params = $request->only(array_keys(ServerTrojanSave::RULES));
        $params['group_id'] = json_encode($params['group_id']);
        if (isset($params['tags'])) {
            $params['tags'] = json_encode($params['tags']);
        }

        if ($request->input('id')) {
            $server = ServerTrojan::find($request->input('id'));
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

        if (!ServerTrojan::create($params)) {
            abort(500, '创建失败');
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
                abort(500, '节点ID不存在');
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
        $server = ServerTrojan::find($request->input('id'));
        $server->show = 0;
        if (!$server) {
            abort(500, '服务器不存在');
        }
        if (!ServerTrojan::create($server->toArray())) {
            abort(500, '复制失败');
        }

        return response([
            'data' => true
        ]);
    }

    public function sort(ServerTrojanSort $request)
    {
        DB::beginTransaction();
        foreach ($request->input('server_ids') as $k => $v) {
            if (!ServerTrojan::find($v)->update(['sort' => $k + 1])) {
                DB::rollBack();
                abort(500, '保存失败');
            }
        }
        DB::commit();
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
