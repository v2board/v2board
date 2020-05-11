<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServerLog extends Model
{
    protected $table = 'v2_server_log';
    protected $dateFormat = 'U';
    protected $dispatchesEvents = [
    ];
}
