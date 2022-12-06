<?php

namespace App\Http\Controllers\Staff;

use App\Http\Requests\Admin\NoticeSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Support\Facades\Cache;

class NoticeController extends Controller
{
    public function fetch(Request $request)
    {
        return response([
            'data' => Notice::orderBy('id', 'DESC')->get()
        ]);
    }

    public function save(NoticeSave $request)
    {
        $data = $request->only([
            'title',
            'content',
            'img_url'
        ]);
        if (!$request->input('id')) {
            if (!Notice::create($data)) {
                abort(500, 'Failed to save');
            }
        } else {
            try {
                Notice::find($request->input('id'))->update($data);
            } catch (\Exception $e) {
                abort(500, 'Failed to save');
            }
        }
        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, 'Parameter error');
        }
        $notice = Notice::find($request->input('id'));
        if (!$notice) {
            abort(500, 'Announcement does not exist');
        }
        if (!$notice->delete()) {
            abort(500, 'Failed to delete');
        }
        return response([
            'data' => true
        ]);
    }
}
