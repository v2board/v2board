<?php

namespace App\Http\Controllers\Server;

use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use App\Utils\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServerShadowsocks;
use App\Models\ServerV2ray;
use App\Models\ServerTrojan;
use Illuminate\Support\Facades\Cache;

class UniProxyController extends Controller
{
    private $nodeType;
    private $nodeInfo;
    private $nodeId;
    private $token;

    public function __construct(Request $request)
    {
        $token = $request->input('token');
        if (empty($token)) {
            abort(500, 'token is null');
        }
        if ($token !== config('v2board.server_token')) {
            abort(500, 'token is error');
        }
        $this->token = $token;
        $this->nodeType = $request->input('node_type');
        $this->nodeId = $request->input('node_id');
        switch ($this->nodeType) {
            case 'v2ray':
                $this->nodeInfo = ServerV2ray::find($this->nodeId);
                break;
            case 'shadowsocks':
                $this->nodeInfo = ServerShadowsocks::find($this->nodeId);
                break;
            case 'trojan':
                $this->nodeInfo = ServerTrojan::find($this->nodeId);
                break;
            default:
                break;
        }
        if (!$this->nodeInfo) {
            abort(500, 'server not found');
        }
    }

    // 后端获取用户
    public function user(Request $request)
    {
        ini_set('memory_limit', -1);
        Cache::put(CacheKey::get('SERVER_' . strtoupper($this->nodeType) . '_LAST_CHECK_AT', $this->nodeInfo->id), time(), 3600);
        $serverService = new ServerService();
        $users = $serverService->getAvailableUsers($this->nodeInfo->group_id);
        $users = $users->toArray();

        $response['users'] = $users;

        $eTag = sha1(json_encode($response));
        if (strpos($request->header('If-None-Match'), $eTag) !== false ) {
            abort(304);
        }

        return response($response)->header('ETag', "\"{$eTag}\"");
    }

    // 后端提交数据
    public function push(Request $request)
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        Cache::put(CacheKey::get('SERVER_' . strtoupper($this->nodeType) . '_ONLINE_USER', $this->nodeInfo->id), count($data), 3600);
        Cache::put(CacheKey::get('SERVER_' . strtoupper($this->nodeType) . '_LAST_PUSH_AT', $this->nodeInfo->id), time(), 3600);
        $userService = new UserService();
        foreach (array_keys($data) as $k) {
            $u = $data[$k]['Upload'];
            $d = $data[$k]['Download'];
            $userService->trafficFetch($u, $d, $k, $this->nodeInfo->toArray(), $this->nodeType);
        }

        return response([
            'data' => true
        ]);
    }

    // 后端获取配置
    public function config(Request $request)
    {
        switch ($this->nodeType) {
            case 'shadowsocks':
                $response = [
                    'server_port' => $this->nodeInfo->server_port,
                    'cipher' => $this->nodeInfo->cipher,
                    'obfs' => $this->nodeInfo->obfs,
                    'obfs_settings' => $this->nodeInfo->obfs_settings
                ];
                break;
            case 'v2ray':
                $response = [
                    'server_port' => $this->nodeInfo->server_port,
                    'network' => $this->nodeInfo->network,
                    'cipher' => $this->nodeInfo->cipher,
                    'networkSettings' => $this->nodeInfo->networkSettings,
                    'tls' => $this->nodeInfo->tls
                ];
                break;
            case 'trojan':
                $response = [
                    'host' => $this->nodeInfo->host,
                    'server_port' => $this->nodeInfo->server_port,
                    'server_name' => $this->nodeInfo->server_name
                ];
                break;
        }
        $response['base_config'] = [
            'push_interval' => 120,
            'pull_interval' => 120
        ];
        $eTag = sha1(json_encode($response));
        if (strpos($request->header('If-None-Match'), $eTag) !== false ) {
            abort(304);
        }

        return response($response)->header('ETag', "\"{$eTag}\"");
    }
}
