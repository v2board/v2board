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
            'id' => 'required',
            'sort' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'id.required' => '教程ID不能为空',
            'sort.required' => '排序不能为空',
            'sort.integer' => '排序格式有误'
        ];
    }
}
