<?php

namespace App\Http\Controllers\Admin\Server;

use App\Http\Requests\Admin\ServerShadowsocksSave;
use App\Http\Requests\Admin\ServerShadowsocksSort;
use App\Http\Requests\Admin\ServerShadowsocksUpdate;
use App\Http\Requests\Admin\ServerV2raySave;
use App\Http\Requests\Admin\ServerV2raySort;
use App\Http\Requests\Admin\ServerV2rayUpdate;
use App\Models\ServerShadowsocks;
use App\Services\ServerService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ShadowsocksController extends Controller
{
    public function fetch(Request $request)
    {
        $server = ServerShadowsocks::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            if (!empty($server[$i]['tags'])) {
                $server[$i]['tags'] = json_decode($server[$i]['tags']);
            }
            $server[$i]['group_id'] = json_decode($server[$i]['group_id']);
            $server[$i]['online'] = Cache::get(CacheKey::get('SERVER_SHADOWSOCKS_ONLINE_USER', $server[$i]['parent_id'] ? $server[$i]['parent_id'] : $server[$i]['id']));
            if ($server[$i]['parent_id']) {
                $server[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_SHADOWSOCKS_LAST_CHECK_AT', $server[$i]['parent_id']));
            } else {
                $server[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_SHADOWSOCKS_LAST_CHECK_AT', $server[$i]['id']));
            }
        }
        return response([
            'data' => $server
        ]);
    }

    public function save(ServerShadowsocksSave $request)
    {
        $params = $request->validated();
        $params['group_id'] = json_encode($params['group_id']);
        if (isset($params['tags'])) {
            $params['tags'] = json_encode($params['tags']);
        }

        if ($request->input('id')) {
            $server = ServerShadowsocks::find($request->input('id'));
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

        if (!ServerShadowsocks::create($params)) {
            abort(500, '创建失败');
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
                abort(500, '节点ID不存在');
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
        $server = ServerShadowsocks::find($request->input('id'));
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

    public function sort(ServerShadowsocksSort $request)
    {
        DB::beginTransaction();
        foreach ($request->input('server_ids') as $k => $v) {
            if (!ServerShadowsocks::find($v)->update(['sort' => $k + 1])) {
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
