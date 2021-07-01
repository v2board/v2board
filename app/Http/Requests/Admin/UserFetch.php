<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserFetch extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'filter.*.key' => 'required|in:id,email,transfer_enable,d,expired_at,uuid,token,invite_by_email,invite_user_id,plan_id,banned,remarks',
            'filter.*.condition' => 'required|in:>,<,=,>=,<=,模糊,!=',
            'filter.*.value' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'filter.*.key.required' => '过滤键不能为空',
            'filter.*.key.in' => '过滤键参数有误',
            'filter.*.condition.required' => '过滤条件不能为空',
            'filter.*.condition.in' => '过滤条件参数有误',
            'filter.*.value.required' => '过滤值不能为空'
        ];
    }
}
