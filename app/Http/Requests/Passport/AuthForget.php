<?php

namespace App\Http\Requests\Passport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class AuthForget extends FormRequest
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
            'email_code' => 'required',
            'password' => [
            'required',
            Password::min(8)->mixedCase()->numbers()->symbols()
            ]
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('Email can not be empty'),
            'email.email' => __('Email format is incorrect'),
            'password.required' => __('Password can not be empty'),
            'password.min' => __('Password must be greater than 8 digits'),
            'email_code.required' => __('Email verification code cannot be empty')
        ];
    }
}
