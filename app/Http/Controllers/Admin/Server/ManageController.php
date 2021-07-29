<?php

namespace App\Http\Controllers\Admin\Server;

use App\Models\Server;
use App\Models\ServerShadowsocks;
use App\Models\ServerTrojan;
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
        DB::beginTransaction();
        foreach ($request->input('sorts') as $k => $v) {
            switch ($v['key']) {
                case 'shadowsocks':
                    if (!ServerShadowsocks::find($v['value'])->update(['sort' => $k + 1])) {
                        DB::rollBack();
                        abort(500, '保存失败');
                    }
                    break;
                case 'v2ray':
                    if (!Server::find($v['value'])->update(['sort' => $k + 1])) {
                        DB::rollBack();
                        abort(500, '保存失败');
                    }
                    break;
                case 'trojan':
                    if (!ServerTrojan::find($v['value'])->update(['sort' => $k + 1])) {
                        DB::rollBack();
                        abort(500, '保存失败');
                    }
                    break;
            }
        }
        DB::commit();
        return response([
            'data' => true
        ]);
    }
}
