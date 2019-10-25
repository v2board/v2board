<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerSave extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'transfer_enable' => 'numeric',
            'expired_at' => 'integer',
            'banned' => 'required|in:0,1',
            'is_admin' => 'required|in:0,1'
        ];
    }
    
    public function messages()
    {
        return [
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'password.required'  => '密码不能为空',
            'transfer_enable.numeric' => '流量格式不正确',
            'expired_at.integer' => '到期时间格式不正确',
            'banned.required' => '是否封禁不能为空',
            'banned.in' => '是否封禁格式不正确',
            'is_admin.required' => '是否管理员不能为空',
            'is_admin.in' => '是否管理员格式不正确'
        ];
    }
}
