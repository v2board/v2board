<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponLog extends Model
{
    protected $table = 'v2_coupon_log';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
