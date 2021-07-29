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
            'old_password.required' => __('Old password cannot be empty'),
            'new_password.required' => __('New password cannot be empty'),
            'new_password.min' => __('Password must be greater than 8 digits')
        ];
    }
}
