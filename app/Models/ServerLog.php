<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerLog extends Model
{
    protected $table = 'v2_server_log';
    protected $dateFormat = 'U';
}
