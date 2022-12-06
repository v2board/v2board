<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserGenerate extends FormRequest
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
            'expired_at' => 'nullable|integer',
            'plan_id' => 'nullable|integer',
            'email_prefix' => 'nullable',
            'email_suffix' => 'required',
            'password' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'generate_count.integer' => 'Generated quantity must be numeric',
            'generate_count.max' => 'The maximum number of generated is 500'
        ];
    }
}
