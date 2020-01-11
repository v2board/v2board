<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TutorialSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tutorial;

class TutorialController extends Controller
{
    public function fetch(Request $request)
    {
        return response([
            'data' => Tutorial::all()
        ]);
    }

    public function save(TutorialSave $request)
    {
        $params = $request->only([
            'title',
            'description',
            'steps',
            'icon'
        ]);

        if (!$request->input('id')) {
            if (!Tutorial::create($params)) {
                abort(500, '创建失败');
            }
        } else {
            if (!Tutorial::find($request->input('id'))->update($params)) {
                abort(500, '保存失败');
            }
        }

        return response([
            'data' => true
        ]);
    }

    public function show(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, '参数有误');
        }
        $tutorial = Tutorial::find($request->input('id'));
        if (!$tutorial) {
            abort(500, '教程不存在');
        }
        $tutorial->show = $tutorial->show ? 0 : 1;
        if (!$tutorial->save()) {
            abort(500, '保存失败');
        }

        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, '参数有误');
        }
        $tutorial = Tutorial::find($request->input('id'));
        if (!$tutorial) {
            abort(500, '教程不存在');
        }
        if (!$tutorial->delete()) {
            abort(500, '删除失败');
        }

        return response([
            'data' => true
        ]);
    }
}
