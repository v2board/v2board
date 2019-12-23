<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Utils\Helper;

class NoticeController extends Controller
{
    public function getNotice (Request $request) {
        return response([
            'data' => Notice::orderBy('created_at', 'DESC')->first()
        ]);
    }
}
