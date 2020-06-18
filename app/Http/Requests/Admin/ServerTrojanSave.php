<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerTrojanSave extends FormRequest
{
    CONST RULES = [
        'show' => '',
        'name' => 'required',
        'group_id' => 'required|array',
        'host' => 'required|regex:/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i',
        'port' => 'required',
        'server_port' => 'required',
        'tags' => 'nullable|array',
        'rate' => 'required|numeric'
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
            'name.required' => '节点名称不能为空',
            'group_id.required' => '权限组不能为空',
            'group_id.array' => '权限组格式不正确',
            'host.required' => '节点地址不能为空',
            'host.regex' => '节点地址必须为域名',
            'port.required' => '连接端口不能为空',
            'server_port.required' => '后端服务端口不能为空',
            'tags.array' => '标签格式不正确',
            'rate.required' => '倍率不能为空',
            'rate.numeric' => '倍率格式不正确'
        ];
    }
}
