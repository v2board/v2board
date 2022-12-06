<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerTrojanSave extends FormRequest
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
            'allow_insecure' => 'nullable|in:0,1',
            'server_name' => 'nullable',
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
            'allow_insecure.in' => 'Allow insecure incorrect formatting',
            'tags.array' => 'Incorrect label format',
            'rate.required' => 'Multiplier cannot be empty',
            'rate.numeric' => 'Incorrect multiplier format'
        ];
    }
}
