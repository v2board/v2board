<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerV2raySort extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'server_ids' => 'required|array'
        ];
    }

    public function messages()
    {
        return [
            'server_ids.required' => '服务器ID不能为空',
            'server_ids.array' => '服务器ID格式有误'
        ];
    }
}
