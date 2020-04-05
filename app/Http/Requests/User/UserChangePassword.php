<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserChangePassword extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_password' => 'required',
            'new_password' => 'required|min:8'
        ];
    }

    public function messages()
    {
        return [
            'old_password.required' => '旧密码不能为空',
            'new_password.required' => '新密码不能为空',
            'new_password.min' => '密码必须大于8位数'
        ];
    }
}
