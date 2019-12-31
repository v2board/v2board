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
            'expired_at' => 'required|integer',
            'limit_use' => 'nullable|integer'
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
            'expired_at.required' => '过期时间不能为空',
            'expired_at.integer' => '过期时间格式有误',
            'limit_use.integer' => '使用次数格式有误'
        ];
    }
}
