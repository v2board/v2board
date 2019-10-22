<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerSave extends FormRequest
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
            'group_id' => 'required|array',
            'host' => 'required',
            'port' => 'required',
            'local_port' => 'required',
            'tls' => 'required',
            'tags' => 'array',
            'rate' => 'required|numeric'
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => '节点名称不能为空',
            'group_id.required'  => '权限组不能为空',
            'group_id.array' => '权限组格式不正确',
            'host.required' => '节点地址不能为空',
            'port.required' => '连接端口不能为空',
            'local_port.required' => '后端服务端口不能为空',
            'tls.required' => 'TLS不能为空',
            'tags.array' => '标签格式不正确',
            'rate.required' => '倍率不能为空',
            'rate.numeric' => '倍率格式不正确'
        ];
    }
}
