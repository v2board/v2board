<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TutorialSave extends FormRequest
{
    CONST RULES = [
        'title' => 'required',
        // 1:windows 2:macos 3:ios 4:android 5:linux 6:router
        'category' => 'required|in:1,2,3,4,5,6',
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
