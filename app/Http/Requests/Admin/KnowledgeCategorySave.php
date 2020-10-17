<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KnowledgeCategorySave extends FormRequest
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
            'language' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '分类名称不能为空',
            'language.required' => '分类语言不能为空'
        ];
    }
}
