<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\KnowledgeSave;
use App\Http\Requests\Admin\KnowledgeSort;
use App\Models\Knowledge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KnowledgeController extends Controller
{
    public function fetch(Request $request)
    {
        if ($request->input('id')) {
            $knowledge = Knowledge::find($request->input('id'))->toArray();
            if (!$knowledge) abort(500, 'Knowledge does not exist');
            return response([
                'data' => $knowledge
            ]);
        }
        return response([
            'data' => Knowledge::select(['title', 'id', 'updated_at', 'category', 'show'])
                ->orderBy('sort', 'ASC')
                ->get()
        ]);
    }

    public function getCategory(Request $request)
    {
        return response([
            'data' => array_keys(Knowledge::get()->groupBy('category')->toArray())
        ]);
    }

    public function save(KnowledgeSave $request)
    {
        $params = $request->validated();

        if (!$request->input('id')) {
            if (!Knowledge::create($params)) {
                abort(500, 'Failed to create');
            }
        } else {
            try {
                Knowledge::find($request->input('id'))->update($params);
            } catch (\Exception $e) {
                abort(500, 'Failed to update');
            }
        }

        return response([
            'data' => true
        ]);
    }

    public function show(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, 'Wrong parameters');
        }
        $knowledge = Knowledge::find($request->input('id'));
        if (!$knowledge) {
            abort(500, 'Knowledge does not exist');
        }
        $knowledge->show = $knowledge->show ? 0 : 1;
        if (!$knowledge->save()) {
            abort(500, 'Failed to save');
        }

        return response([
            'data' => true
        ]);
    }

    public function sort(KnowledgeSort $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->input('knowledge_ids') as $k => $v) {
                $knowledge = Knowledge::find($v);
                $knowledge->timestamps = false;
                $knowledge->update(['sort' => $k + 1]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, 'Failed to save');
        }
        DB::commit();
        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, 'Wrong parameters');
        }
        $knowledge = Knowledge::find($request->input('id'));
        if (!$knowledge) {
            abort(500, 'Knowledge does not exist');
        }
        if (!$knowledge->delete()) {
            abort(500, 'Failed to delete');
        }

        return response([
            'data' => true
        ]);
    }
}
