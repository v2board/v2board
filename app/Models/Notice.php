<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'v2_notice';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
