<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KnowledgeCategorySort extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'knowledge_category_ids' => 'required|array'
        ];
    }

    public function messages()
    {
        return [
            'knowledge_category_ids.required' => 'Category cannot be empty',
            'knowledge_category_ids.array' => 'Wrong category format'
        ];
    }
}
