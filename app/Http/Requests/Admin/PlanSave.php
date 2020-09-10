<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlanSave extends FormRequest
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
            'content' => '',
            'group_id' => 'required',
            'transfer_enable' => 'required',
            'month_price' => 'nullable|integer',
            'quarter_price' => 'nullable|integer',
            'half_year_price' => 'nullable|integer',
            'year_price' => 'nullable|integer',
            'two_year_price' => 'nullable|integer',
            'three_year_price' => 'nullable|integer',
            'onetime_price' => 'nullable|integer',
            'reset_price' => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '套餐名称不能为空',
            'type.required' => '套餐类型不能为空',
            'type.in' => '套餐类型格式有误',
            'group_id.required' => '权限组不能为空',
            'transfer_enable.required' => '流量不能为空',
            'month_price.integer' => '月付金额格式有误',
            'quarter_price.integer' => '季付金额格式有误',
            'half_year_price.integer' => '半年付金额格式有误',
            'year_price.integer' => '年付金额格式有误',
            'two_year_price.integer' => '两年付金额格式有误',
            'three_year_price.integer' => '三年付金额格式有误',
            'onetime_price.integer' => '一次性金额有误',
            'reset_price.integer' => '流量重置包金额有误'
        ];
    }
}
