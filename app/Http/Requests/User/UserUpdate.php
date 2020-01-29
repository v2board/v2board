<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'remind_expire' => 'in:0,1',
            'remind_traffic' => 'in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'show.in' => '过期提醒格式不正确',
            'renew.in' => '流量提醒格式不正确'
        ];
    }
}
