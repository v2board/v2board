<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlanSort extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'plan_ids' => 'required|array'
        ];
    }

    public function messages()
    {
        return [
            'plan_ids.required' => 'Subscription plan ID cannot be empty',
            'plan_ids.array' => 'Wrong subscription plan ID format'
        ];
    }
}
