<?php

namespace App\Http\Controllers\Admin\Server;

use App\Http\Requests\Admin\ServerShadowsocksSave;
use App\Http\Requests\Admin\ServerShadowsocksUpdate;
use App\Models\ServerRoute;
use App\Models\ServerShadowsocks;
use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RouteController extends Controller
{
    public function fetch(Request $request)
    {
        $routes = ServerRoute::get();
        return [
            'data' => $routes
        ];
    }

    public function save(Request $request)
    {
        $params = $request->validate([
            'remarks' => 'required',
            'match' => 'required',
            'action' => 'required',
            'action_value' => 'nullable'
        ]);
        if ($request->input('id')) {
            try {
                $route = ServerRoute::find($request->input('id'));
                $route->update($params);
                return [
                    'data' => true
                ];
            } catch (\Exception $e) {
                abort(500, '保存失败');
            }
        }
        if (!ServerRoute::create($params)) abort(500, '创建失败');
        return [
            'data' => true
        ];
    }

    public function drop(Request $request)
    {
        $route = ServerRoute::find($request->input('id'));
        if (!$route) abort(500, '路由不存在');
        if (!$route->delete()) abort(500, '删除失败');
        return [
            'data' => true
        ];
    }
}
