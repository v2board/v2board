<?php

namespace App\Http\Controllers\Admin\Server;

use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ManageController extends Controller
{
    public function getNodes(Request $request)
    {
        $serverService = new ServerService();
        return response([
            'data' => $serverService->getAllServers()
        ]);
    }

    public function sort(Request $request)
    {
        ini_set('post_max_size', '1m');
        $params = $request->only(
                'shadowsocks',
                'vmess',
                'trojan',
                'hysteria'
            ) ?? [];
        DB::beginTransaction();
        foreach ($params as $k => $v) {
            $model = 'App\\Models\\Server' . ucfirst($k);
            foreach($v as $id => $sort) {
                if (!$model::find($id)->update(['sort' => $sort])) {
                    DB::rollBack();
                    abort(500, '保存失败');
                }
            }
        }
        DB::commit();
        return response([
            'data' => true
        ]);
    }
}
