<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class OrderSave extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'plan_id' => 'required',
            'cycle' => 'required|in:month_price,quarter_price,half_year_price,year_price,two_year_price,three_year_price,onetime_price,reset_price'
        ];
    }

    public function messages()
    {
        return [
            'plan_id.required' => '套餐ID不能为空',
            'cycle.required' => '套餐周期不能为空',
            'cycle.in' => '套餐周期有误'
        ];
    }
}
