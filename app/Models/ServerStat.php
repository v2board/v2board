<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerStat extends Model
{
    protected $table = 'v2_server_stat';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
