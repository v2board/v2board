<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerTrojan extends Model
{
    protected $table = 'v2_server_trojan';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'group_id' => 'array',
        'route_id' => 'array',
        'tags' => 'array'
    ];
}
