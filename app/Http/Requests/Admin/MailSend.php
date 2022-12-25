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
            'type.required' => '发送类型不能为空',
            'type.in' => '发送类型格式有误',
            'subject.required' => '主题不能为空',
            'content.required' => '内容不能为空',
            'receiver.array' => '收件人格式有误'
        ];
    }
}
