<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TutorialSave extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'description' => 'required',
            'icon' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => '标题不能为空',
            'description.required' => '描述不能为空',
            'icon.required' => '图标不能为空'
        ];
    }
}
