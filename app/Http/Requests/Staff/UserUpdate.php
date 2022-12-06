<?php

namespace App\Http\Requests\Staff;

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
            'email' => 'required|email',
            'password' => 'nullable',
            'transfer_enable' => 'numeric',
            'expired_at' => 'nullable|integer',
            'banned' => 'required|in:0,1',
            'plan_id' => 'nullable|integer',
            'commission_rate' => 'nullable|integer|min:0|max:100',
            'discount' => 'nullable|integer|min:0|max:100',
            'u' => 'integer',
            'd' => 'integer',
            'balance' => 'integer',
            'commission_balance' => 'integer'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'E-mail can not be empty',
            'email.email' => 'E-mail format is incorrect',
            'transfer_enable.numeric' => 'Incorrect traffic format',
            'expired_at.integer' => 'Expiration time format is incorrect',
            'banned.required' => 'Whether the ban cannot be empty',
            'banned.in' => 'Whether the blocking format is incorrect',
            'plan_id.integer' => 'Incorrect format of subscription plan',
            'commission_rate.integer' => 'Referral rebate percentage format is incorrect',
            'commission_rate.nullable' => 'Referral rebate percentage format is incorrect',
            'commission_rate.min' => 'Minimum referral rebate percentage is 0',
            'commission_rate.max' => 'Referral rebate percentage up to 100',
            'discount.integer' => 'The exclusive discount percentage is incorrectly formatted',
            'discount.nullable' => 'The exclusive discount percentage is incorrectly formatted',
            'discount.min' => 'Exclusive discount percentage is minimum 0',
            'discount.max' => 'Exclusive discount percentage up to 100',
            'u.integer' => 'Incorrect format of uplink traffic',
            'd.integer' => 'Incorrect format of downlink traffic',
            'balance.integer' => 'The balance is not in the correct format',
            'commission_balance.integer' => 'Incorrect commission format'
        ];
    }
}
