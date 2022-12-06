<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => 'in:0,1,2,3',
            'commission_status' => 'in:0,1,3'
        ];
    }

    public function messages()
    {
        return [
            'status.in' => 'Incorrect sales status format',
            'commission_status.in' => 'Incorrect commission status format'
        ];
    }
}
