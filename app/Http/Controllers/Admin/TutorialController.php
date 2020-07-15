<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TutorialSave;
use App\Http\Requests\Admin\TutorialSort;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Support\Facades\DB;

class TutorialController extends Controller
{
    public function fetch(Request $request)
    {
        return response([
            'data' => Tutorial::orderBy('sort', 'ASC')->get()
        ]);
    }

    public function save(TutorialSave $request)
    {
        $params = $request->validated();

        if (!$request->input('id')) {
            if (!Tutorial::create($params)) {
                abort(500, '创建失败');
            }
        } else {
            try {
                Tutorial::find($request->input('id'))->update($params);
            } catch (\Exception $e) {
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

    public function sort(TutorialSort $request)
    {
        DB::beginTransaction();
        foreach ($request->input('tutorial_ids') as $k => $v) {
            if (!Tutorial::find($v)->update(['sort' => $k + 1])) {
                DB::rollBack();
                abort(500, '保存失败');
            }
        }
        DB::commit();
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
