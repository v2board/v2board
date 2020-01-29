<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class TicketSave extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'subject' => 'required',
            'level' => 'required|in:0,1,2',
            'message' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'subject.required' => '工单主题不能为空',
            'level.required' => '工单级别不能为空',
            'level.in' => '工单级别格式不正确',
            'message.required' => '消息不能为空'
        ];
    }
}
