<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TutorialSave extends FormRequest
{
    CONST RULES = [
        'title' => 'required',
        'category' => 'required|in:windows,macos,ios,android,linux,router',
        'description' => 'required',
        'icon' => 'required'
    ];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return self::RULES;
    }

    public function messages()
    {
        return [
            'title.required' => '标题不能为空',
            'category.required' => '分类不能为空',
            'category.in' => '分类格式不正确',
            'description.required' => '描述不能为空',
            'icon.required' => '图标不能为空'
        ];
    }
}
