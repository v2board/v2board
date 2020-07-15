<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponSave extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'type' => 'required|in:1,2',
            'value' => 'required|integer',
            'started_at' => 'required|integer',
            'ended_at' => 'required|integer',
            'limit_use' => 'nullable|integer',
            'limit_plan_ids' => 'nullable|array',
            'code' => ''
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名称不能为空',
            'type.required' => '类型不能为空',
            'type.in' => '类型格式有误',
            'value.required' => '金额或比例不能为空',
            'value.integer' => '金额或比例格式有误',
            'started_at.required' => '开始时间不能为空',
            'started_at.integer' => '开始时间格式有误',
            'ended_at.required' => '结束时间不能为空',
            'ended_at.integer' => '结束时间格式有误',
            'limit_use.integer' => '使用次数格式有误',
            'limit_plan_ids.array' => '指定订阅格式有误'
        ];
    }
}
