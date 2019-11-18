<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $table = 'v2_server';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
