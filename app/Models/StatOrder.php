<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatOrder extends Model
{
    protected $table = 'v2_stat_order';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
