<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $table = 'v2_mail_log';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
