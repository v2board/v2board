<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponGenerate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'generate_count' => 'nullable|integer|max:500',
            'name' => 'required',
            'type' => 'required|in:1,2',
            'value' => 'required|integer',
            'started_at' => 'required|integer',
            'ended_at' => 'required|integer',
            'limit_use' => 'nullable|integer',
            'limit_use_with_user' => 'nullable|integer',
            'limit_plan_ids' => 'nullable|array',
            'limit_period' => 'nullable|array',
            'code' => ''
        ];
    }

    public function messages()
    {
        return [
            'generate_count.integer' => 'Generated quantity must be numeric',
            'generate_count.max' => 'The maximum number of generated is 500',
            'name.required' => 'Name cannot be empty',
            'type.required' => 'Type cannot be empty',
            'type.in' => 'Wrong type format',
            'value.required' => 'The amount or percentage cannot be empty',
            'value.integer' => 'Incorrect amount or ratio format',
            'started_at.required' => 'Start time cannot be empty',
            'started_at.integer' => 'Wrong start time format',
            'ended_at.required' => 'End time cannot be empty',
            'ended_at.integer' => 'The end time format is wrong',
            'limit_use.integer' => 'The maximum number of uses is incorrectly formatted',
            'limit_use_with_user.integer' => 'Wrong format for limiting the number of times a user can use',
            'limit_plan_ids.array' => 'The specified subscription format is incorrect',
            'limit_period.array' => 'Wrong format for the specified period'
        ];
    }
}
