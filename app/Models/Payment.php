<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'v2_payment';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
