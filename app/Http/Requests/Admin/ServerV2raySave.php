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
            'name.required' => 'Node name cannot be empty',
            'group_id.required' => 'groups id cannot be empty',
            'group_id.array' => 'group id format is incorrect',
            'parent_id.integer' => 'The parent ID is not in the correct format',
            'host.required' => 'Node address cannot be empty',
            'port.required' => 'The connection port cannot be empty',
            'server_port.required' => 'The back-end service port cannot be empty',
            'tls.required' => 'TLS cannot be empty',
            'tags.array' => 'Incorrect label format',
            'rate.required' => 'Multiplier cannot be empty',
            'rate.numeric' => 'Incorrect multiplier format',
            'network.required' => 'Transfer protocol cannot be null',
            'network.in' => 'Incorrect transfer protocol format',
            'networkSettings.array' => 'The transmission protocol is incorrectly configured',
            'ruleSettings.array' => 'Wrong rule configuration',
            'tlsSettings.array' => 'tls configuration error',
            'dnsSettings.array' => 'Wrong dns configuration'
        ];
    }
}
