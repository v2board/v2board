<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class TicketWithdraw  extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'withdraw_method' => 'required|in:alipay,paypal,usdt,btc',
            'withdraw_account' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'withdraw_method.required' => '提现方式不能为空',
            'withdraw_method.in' => '提现方式不支持',
            'withdraw_account.required' => '提现账号不能为空'
        ];
    }
}
