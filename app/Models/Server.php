<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $table = 'v2_server';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'group_id' => 'array',
        'tlsSettings' => 'array',
        'networkSettings' => 'array',
        'dnsSettings' => 'array',
        'ruleSettings' => 'array',
        'tags' => 'array'
    ];
}
