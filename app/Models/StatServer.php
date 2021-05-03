<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatServer extends Model
{
    protected $table = 'v2_stat_server';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
