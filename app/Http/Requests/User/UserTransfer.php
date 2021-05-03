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
            'transfer_amount.required' => '划转金额不能为空',
            'transfer_amount.integer' => __('user.user.transfer.params_wrong'),
            'transfer_amount.min' => __('user.user.transfer.params_wrong')
        ];
    }
}
