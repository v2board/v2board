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
            'group_id' => 'required',
            'transfer_enable' => 'required',
            'month_price' => 'required|numeric',
            'quarter_price' => 'required|numeric',
            'half_year_price' => 'required|numeric',
            'year_price' => 'required|numeric'
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => '套餐名称不能为空',
            'group_id.required' => '权限组不能为空',
            'transfer_enable.required' => '流量不能为空',
            'month_price.required' => '月付金额不能为空',
            'quarter_price.required' => '季付金额不能为空',
            'half_year_price.required' => '半年付金额不能为空',
            'year_price.required' => '年付金额不能为空'
        ];
    }
}
