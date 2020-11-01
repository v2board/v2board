<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerShadowsocks extends Model
{
    protected $table = 'v2_server_shadowsocks';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
