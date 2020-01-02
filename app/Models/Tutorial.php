<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $table = 'v2_tutorial';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
