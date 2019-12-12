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
            'data' => Notice::orderBy('id', 'DESC')->get()
        ]);
    }

    public function save (NoticeSave $request) {
        $data = $request->only([
            'title',
            'content'
        ]);
        if (!Notice::create($data)) {
            abort(500, '保存失败');
        }
        return response([
            'data' => true
        ]);
    }

    public function update (NoticeSave $request) {
        $data = $request->only([
            'title',
            'content'
        ]);
        if (!Notice::where('id', $request->input('id'))->update($data)) {
            abort(500, '保存失败');
        }
        return response([
            'data' => true
        ]);
    }
}
