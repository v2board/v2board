<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KnowledgeSort extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'knowledge_ids' => 'required|array'
        ];
    }

    public function messages()
    {
        return [
            'knowledge_ids.required' => '知识ID不能为空',
            'knowledge_ids.array' => '知识ID格式有误'
        ];
    }
}
