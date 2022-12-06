<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MailSend extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|in:1,2,3,4',
            'subject' => 'required',
            'content' => 'required',
            'receiver' => 'array'
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Send type cannot be empty',
            'type.in' => 'Wrong send type format',
            'subject.required' => 'Subject cannot be empty',
            'content.required' => 'Content cannot be empty',
            'receiver.array' => 'Incorrect recipient format'
        ];
    }
}
