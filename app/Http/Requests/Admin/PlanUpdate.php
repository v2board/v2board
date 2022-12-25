<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlanUpdate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'show' => 'in:0,1',
            'renew' => 'in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'show.in' => '销售状态格式不正确',
            'renew.in' => '续费状态格式不正确'
        ];
    }
}
