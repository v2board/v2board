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
            'filter.*.key' => 'required|in:email,transfer_enable,d,expired_at,uuid,token',
            'filter.*.condition' => 'required|in:>,<,=,>=,<=',
            'filter.*.value' => 'required'
        ];
    }

    public function messages()
    {
        return [
        ];
    }
}
