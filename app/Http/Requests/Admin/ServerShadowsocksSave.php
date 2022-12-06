<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerShadowsocksSave extends FormRequest
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
            'cipher' => 'required|in:aes-128-gcm,aes-256-gcm,chacha20-ietf-poly1305',
            'obfs' => 'nullable|in:http',
            'obfs_settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'rate' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Node name cannot be empty',
            'group_id.required' => 'groups id cannot be empty',
            'group_id.array' => 'group id format is incorrect',
            'parent_id.integer' => 'Parent node format is incorrect',
            'host.required' => 'Node address cannot be empty',
            'port.required' => 'The connection port cannot be empty',
            'server_port.required' => 'The back-end service port cannot be empty',
            'cipher.required' => 'Encryption method cannot be empty',
            'tags.array' => 'Incorrect label format',
            'rate.required' => 'Multiplier cannot be empty',
            'rate.numeric' => 'Incorrect multiplier format',
            'obfs.in' => 'obfuscation format is incorrect',
            'obfs_settings.array' => 'Obfuscation settings are incorrectly formatted'
        ];
    }
}
