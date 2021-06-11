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
            'show.in' => __('Incorrect format of expiration reminder'),
            'renew.in' => __('Incorrect traffic alert format')
        ];
    }
}
