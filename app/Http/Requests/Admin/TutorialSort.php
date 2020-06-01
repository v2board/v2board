<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TutorialSort extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tutorial_ids' => 'required|array'
        ];
    }

    public function messages()
    {
        return [
            'tutorial_ids.required' => '教程ID不能为空',
            'tutorial_ids.array' => '教程ID格式有误'
        ];
    }
}
