<?php

namespace App\Http\Controllers\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Server\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;
use App\Models\ServerLog;
use Illuminate\Support\Facades\Log;

class DeepbworkController extends Controller
{
    CONST SERVER_CONFIG = '{"api":{"services":["HandlerService","StatsService"],"tag":"api"},"stats":{},"inbound":{"port":443,"protocol":"vmess","settings":{"clients":[]},"streamSettings":{"network":"tcp"},"tag":"proxy"},"inboundDetour":[{"listen":"0.0.0.0","port":23333,"protocol":"dokodemo-door","settings":{"address":"0.0.0.0"},"tag":"api"}],"log":{"loglevel":"debug","access":"access.log","error":"error.log"},"outbound":{"protocol":"freedom","settings":{}},"routing":{"settings":{"rules":[{"inboundTag":["api"],"outboundTag":"api","type":"field"}]},"strategy":"rules"},"policy":{"levels":{"0":{"handshake":4,"connIdle":300,"uplinkOnly":5,"downlinkOnly":30,"statsUserUplink":true,"statsUserDownlink":true}}}}';
    // 后端获取用户
    public function user (Request $request) {
        $nodeId = $request->input('node_id');
        $server = Server::find($nodeId);
        $server->last_check_at = time();
        $users = User::whereIn('group_id', json_decode($server->group_id))
            ->select([
                'id',
                'email',
                't',
                'u',
                'd',
                'transfer_enable',
                'enable',
                'v2ray_uuid',
                'v2ray_alter_id',
                'v2ray_level'
            ])
            ->get();
        $result = [];
        foreach ($users as $user) {
            $user->v2ray_user = [
                "uuid" => $user->v2ray_uuid,
                "email" => sprintf("%s@v2panel.user", $user->v2ray_uuid),
                "alter_id" => $user->v2ray_alter_id,
                "level" => $user->v2ray_level,
            ];
            unset($user['v2ray_uuid']);
            unset($user['v2ray_alter_id']);
            unset($user['v2ray_level']);
            array_push($result, $user);
        }
        return response([
            'msg' => 'ok',
            'data' => $result,
        ]);
    }

    // 后端提交数据
    public function submit (Request $request) {
		Log::info('serverSubmitData:' . $request->input('node_id') . ':' . file_get_contents('php://input'));
        $server = Server::find($request->input('node_id'));
        if (!$server) {
        	return response([
        		'ret' => 1,
        		'msg' => 'ok'
        	]);
        }
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        foreach ($data as $item) {
        	$u = $item['u'] * $server->rate;
			$d = $item['d'] * $server->rate;
			$user = User::find($item['user_id']);
			$user->t = time();
			$user->u = $user->u + $u;
			$user->d = $user->d + $d;
            $user->save();
            
            $serverLog = new ServerLog();
            $serverLog->user_id = $item['user_id'];
            $serverLog->node_id = $request->input('node_id');
            $serverLog->u = $item['u'];
            $serverLog->d = $item['d'];
            $serverLog->rate = $server->rate;
            $serverLog->save();
        }
        
    	return response([
    		'ret' => 1,
    		'msg' => 'ok'
    	]);
    }

    // 后端获取配置
    public function config (Request $request) {
        $nodeId = $request->input('node_id');
        $localPort = $request->input('local_port');
        $server = Server::find($nodeId);
        $jsonData = json_decode(self::SERVER_CONFIG);
        $jsonData->inboundDetour[0]->port = (int)$localPort;
        $jsonData->inbound->port = (int)$server->server_port;
        if ((int)$server->tls) {
            $jsonData->inbound->streamSettings->security = "tls";
            $tls = (object) array("certificateFile" => "/home/v2ray.crt", "keyFile" => "/home/v2ray.key");
            $jsonData->inbound->streamSettings->tlsSettings->certificates[0] = $tls;
        }
        
        die(json_encode($jsonData, JSON_UNESCAPED_UNICODE));
    }
}
