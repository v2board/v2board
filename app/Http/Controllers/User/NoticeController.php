<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Utils\Helper;

class NoticeController extends Controller
{
    public function fetch(Request $request)
    {
        return response([
            'data' => Notice::orderBy('created_at', 'DESC')->first()
        ]);
    }
}
