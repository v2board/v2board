<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdate extends FormRequest
{
    CONST RULES = [
        'email' => 'required|email',
        'password' => 'nullable',
        'transfer_enable' => 'numeric',
        'expired_at' => 'integer',
        'enable' => 'required|in:0,1',
        'plan_id' => 'integer',
        'commission_rate' => 'nullable|integer|min:0|max:100',
        'discount' => 'nullable|integer|min:0|max:100',
        'is_admin' => 'required|in:0,1',
        'u' => 'integer',
        'd' => 'integer',
        'balance' => 'integer',
        'commission_balance' => 'integer'
    ];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return self::RULES;
    }

    public function messages()
    {
        return [
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'transfer_enable.numeric' => '流量格式不正确',
            'expired_at.integer' => '到期时间格式不正确',
            'enable.required' => '账户状态不能为空',
            'enable.in' => '账户状态格式不正确',
            'is_admin.required' => '是否管理员不能为空',
            'is_admin.in' => '是否管理员格式不正确',
            'plan_id.integer' => '订阅计划格式不正确',
            'commission_rate.integer' => '推荐返利比例格式不正确',
            'commission_rate.nullable' => '推荐返利比例格式不正确',
            'commission_rate.min' => '推荐返利比例最小为0',
            'commission_rate.max' => '推荐返利比例最大为100',
            'discount.integer' => '专属折扣比例格式不正确',
            'discount.nullable' => '专属折扣比例格式不正确',
            'discount.min' => '专属折扣比例最小为0',
            'discount.max' => '专属折扣比例最大为100',
            'u.integer' => '上行流量格式不正确',
            'd.integer' => '下行流量格式不正确',
            'balance.integer' => '余额格式不正确',
            'commission_balance.integer' => '佣金格式不正确'
        ];
    }
}
