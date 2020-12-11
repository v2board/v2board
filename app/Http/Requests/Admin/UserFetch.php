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
            'filter.*.key' => 'required|in:id,email,transfer_enable,d,expired_at,uuid,token,invite_by_email,invite_user_id,plan_id',
            'filter.*.condition' => 'required|in:>,<,=,>=,<=,模糊',
            'filter.*.value' => 'required'
        ];
    }

    public function messages()
    {
        return [
        ];
    }
}
