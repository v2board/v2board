<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\NoticeSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Support\Facades\Redis;

class NoticeController extends Controller
{
    public function index (Request $request) {
        return response([
            'data' => Notice::get()
        ]);
    }

    public function save (NoticeSave $request) {
        $data = $request->only([
            'title',
            'content'
        ]);
        return response([
            'data' => Notice::create($data)
        ]);
    }
}
