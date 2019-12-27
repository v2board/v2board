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
            'month_price' => 'numeric',
            'quarter_price' => 'numeric',
            'half_year_price' => 'numeric',
            'year_price' => 'numeric'
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => '套餐名称不能为空',
            'group_id.required' => '权限组不能为空',
            'transfer_enable.required' => '流量不能为空',
            'month_price.numeric' => '月付金额格式有误',
            'quarter_price.numeric' => '季付金额格式有误',
            'half_year_price.numeric' => '半年付金额格式有误',
            'year_price.numeric' => '年付金额格式有误'
        ];
    }
}
