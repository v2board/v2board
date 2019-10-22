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
            'transfer_enable' => 'required'
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => '套餐名称不能为空',
            'group_id.required' => '权限组不能为空',
            'transfer_enable' => '流量不能为空'
        ];
    }
}
