<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlanSave extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'content' => '',
            'group_id' => 'required',
            'transfer_enable' => 'required',
            'month_price' => 'nullable|integer',
            'quarter_price' => 'nullable|integer',
            'half_year_price' => 'nullable|integer',
            'year_price' => 'nullable|integer',
            'two_year_price' => 'nullable|integer',
            'three_year_price' => 'nullable|integer',
            'onetime_price' => 'nullable|integer',
            'reset_price' => 'nullable|integer',
            'reset_traffic_method' => 'nullable|integer|in:0,1,2,3,4',
            'capacity_limit' => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Package name cannot be empty',
            'type.required' => 'Package type cannot be empty',
            'type.in' => 'Wrong format of package type',
            'group_id.required' => 'groups id cannot be empty',
            'transfer_enable.required' => 'Traffic cannot be empty',
            'month_price.integer' => 'The monthly payment amount is incorrectly formatted',
            'quarter_price.integer' => 'The quarterly payment amount is incorrectly formatted',
            'half_year_price.integer' => 'The half year payment amount is incorrectly formatted',
            'year_price.integer' => 'The annual payment amount is incorrectly formatted',
            'two_year_price.integer' => 'The two-year payment amount is incorrectly formatted',
            'three_year_price.integer' => 'The three-year payment amount is incorrectly formatted',
            'onetime_price.integer' => 'One-time amount is incorrect',
            'reset_price.integer' => 'The amount of the traffic reset package is wrong',
            'reset_traffic_method.integer' => 'Wrong format of traffic reset method',
            'reset_traffic_method.in' => 'Wrong format of traffic reset method',
            'capacity_limit.integer' => 'Incorrect format of user capacity limit'
        ];
    }
}
