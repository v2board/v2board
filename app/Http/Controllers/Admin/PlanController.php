<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PlanSave;
use App\Http\Requests\Admin\PlanSort;
use App\Http\Requests\Admin\PlanUpdate;
use App\Services\PlanService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    public function fetch(Request $request)
    {
        $counts = PlanService::countActiveUsers();
        $plans = Plan::orderBy('sort', 'ASC')->get();
        foreach ($plans as $k => $v) {
            $plans[$k]->count = 0;
            foreach ($counts as $kk => $vv) {
                if ($plans[$k]->id === $counts[$kk]->plan_id) $plans[$k]->count = $counts[$kk]->count;
            }
        }
        return response([
            'data' => $plans
        ]);
    }

    public function save(PlanSave $request)
    {
        $params = $request->validated();
        if ($request->input('id')) {
            $plan = Plan::find($request->input('id'));
            if (!$plan) {
                abort(500, 'This subscription does not exist');
            }
            DB::beginTransaction();
            // update user group id and transfer
            try {
                if ($request->input('force_update')) {
                    User::where('plan_id', $plan->id)->update([
                        'group_id' => $params['group_id'],
                        'transfer_enable' => $params['transfer_enable'] * 1073741824,
                        'speed_limit' => $params['speed_limit']
                    ]);
                }
                $plan->update($params);
            } catch (\Exception $e) {
                DB::rollBack();
                abort(500, 'Failed to save');
            }
            DB::commit();
            return response([
                'data' => true
            ]);
        }
        if (!Plan::create($params)) {
            abort(500, 'Failed to create');
        }
        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        if (Order::where('plan_id', $request->input('id'))->first()) {
            abort(500, 'Orders exist under this subscription that cannot be deleted');
        }
        if (User::where('plan_id', $request->input('id'))->first()) {
            abort(500, 'Existing users under this subscription cannot be deleted');
        }
        if ($request->input('id')) {
            $plan = Plan::find($request->input('id'));
            if (!$plan) {
                abort(500, 'This subscription ID does not exist');
            }
        }
        return response([
            'data' => $plan->delete()
        ]);
    }

    public function update(PlanUpdate $request)
    {
        $updateData = $request->only([
            'show',
            'renew'
        ]);

        $plan = Plan::find($request->input('id'));
        if (!$plan) {
            abort(500, 'This subscription does not exist');
        }

        try {
            $plan->update($updateData);
        } catch (\Exception $e) {
            abort(500, 'Failed to save');
        }

        return response([
            'data' => true
        ]);
    }

    public function sort(PlanSort $request)
    {
        DB::beginTransaction();
        foreach ($request->input('plan_ids') as $k => $v) {
            if (!Plan::find($v)->update(['sort' => $k + 1])) {
                DB::rollBack();
                abort(500, 'Failed to save');
            }
        }
        DB::commit();
        return response([
            'data' => true
        ]);
    }
}
