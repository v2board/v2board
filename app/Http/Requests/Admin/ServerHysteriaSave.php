<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerHysteriaSave extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'show' => '',
            'name' => 'required',
            'group_id' => 'required|array',
            'route_id' => 'nullable|array',
            'parent_id' => 'nullable|integer',
            'host' => 'required',
            'port' => 'required',
            'server_port' => 'required',
            'tags' => 'nullable|array',
            'rate' => 'required|numeric',
            'up_mbps' => 'required|numeric|min:1',
            'down_mbps' => 'required|numeric|min:1',
            'server_name' => 'nullable',
            'insecure' => 'required|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '节点名称不能为空',
            'group_id.required' => '权限组不能为空',
            'group_id.array' => '权限组格式不正确',
            'route_id.array' => '路由组格式不正确',
            'parent_id.integer' => '父节点格式不正确',
            'host.required' => '节点地址不能为空',
            'port.required' => '连接端口不能为空',
            'server_port.required' => '后端服务端口不能为空',
            'tags.array' => '标签格式不正确',
            'rate.required' => '倍率不能为空',
            'rate.numeric' => '倍率格式不正确',
            'up_mbps.required' => '上传速度不能为空',
            'up_mbps.numeric' => '上传速度格式不正确',
            'up_mbps.min' => '上传速度必须大于0',
            'down_mbps.required' => '下载速度不能为空',
            'down_mbps.numeric' => '下载速度格式不正确',
            'down_mbps.min' => '下载速度必须大于0',
            'insecure.required' => '安全性不能为空',
            'insecure.in' => '安全性格式不正确',
        ];
    }
}
