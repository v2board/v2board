<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerV2raySave extends FormRequest
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
            'parent_id' => 'nullable|integer',
            'host' => 'required',
            'port' => 'required',
            'server_port' => 'required',
            'tls' => 'required',
            'tags' => 'nullable|array',
            'rate' => 'required|numeric',
            'alter_id' => 'required|integer',
            'network' => 'required|in:tcp,kcp,ws,http,domainsocket,quic,grpc',
            'networkSettings' => 'nullable|array',
            'ruleSettings' => 'nullable|array',
            'tlsSettings' => 'nullable|array',
            'dnsSettings' => 'nullable|array'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '节点名称不能为空',
            'group_id.required' => '权限组不能为空',
            'group_id.array' => '权限组格式不正确',
            'parent_id.integer' => '父ID格式不正确',
            'host.required' => '节点地址不能为空',
            'port.required' => '连接端口不能为空',
            'server_port.required' => '后端服务端口不能为空',
            'tls.required' => 'TLS不能为空',
            'tags.array' => '标签格式不正确',
            'rate.required' => '倍率不能为空',
            'rate.numeric' => '倍率格式不正确',
            'network.required' => '传输协议不能为空',
            'network.in' => '传输协议格式不正确',
            'networkSettings.array' => '传输协议配置有误',
            'ruleSettings.array' => '规则配置有误',
            'tlsSettings.array' => 'tls配置有误',
            'dnsSettings.array' => 'dns配置有误'
        ];
    }
}
