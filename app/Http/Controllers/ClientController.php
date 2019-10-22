<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;

class ClientController extends Controller
{
    public function subscribe (Request $request) {
        $user = $request->user;
        $server = [];
        if ($user->expired_at > time()) {
          $servers = Server::all();
          foreach ($servers as $item) {
              $groupId = json_decode($item['group_id']);
              if (in_array($user->group_id, $groupId)) {
                  array_push($server, $item);
              }
          }
        }
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'Quantumult-X') !== -1) {
          die($this->quantumultX($user, $server));
        }
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'Quantumult') !== -1) {
          die($this->quantumult($user, $server));
        }
        die($this->origin($user, $server));
    }

    private function quantumultX ($user, $server) {
      $uri = '';
      foreach($server as $item) {
        $uri .= "vmess=".$item->host.":".$item->port.", method=none, password=".$user->v2ray_uuid.", fast-open=false, udp-relay=false, tag=".$item->name."\r\n";
      }
      return base64_encode($uri);
    }

    private function quantumult ($user, $server) {
      $uri = '';
      header('subscription-userinfo: upload='.$user->u.'; download='.$user->d.';total='.$user->transfer_enable);
      foreach($server as $item) {
        $uri .= "vmess://".base64_encode($item->name.'= vmess, '.$item->host.', '.$item->port.', chacha20-ietf-poly1305, "'.$user->v2ray_uuid.'", over-tls='.($item->tls?"true":"false").', certificate=1, group='.config('v2panel.app_name', 'V2Panel'))."\r\n";
      }
      return base64_encode($uri);
    }

    private function origin ($user, $server) {
      $uri = '';
      foreach($server as $item) {
        $config = [
          "ps" => $item->name,
          "add" => $item->host,
          "port" => $item->port,
          "id" => $user->v2ray_uuid,
          "aid" => "2",
          "net" => "tcp",
          "type" => "chacha20-poly1305",
          "host" => "",
          "tls" => $item->tls?"tls":"",
        ];
        $uri .= "vmess://".base64_encode(json_encode($config))."\r\n";
      }
      return base64_encode($uri);
    }
}
