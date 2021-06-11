<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserTransfer extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'transfer_amount' => 'required|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'transfer_amount.required' => __('The transfer amount cannot be empty'),
            'transfer_amount.integer' => __('The transfer amount parameter is wrong'),
            'transfer_amount.min' => __('The transfer amount parameter is wrong')
        ];
    }
}
