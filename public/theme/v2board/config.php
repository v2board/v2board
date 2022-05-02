<?php

return [
    'name' => 'V2board',
    'description' => '这是一个描述',
    'version' => '1.5.6',
    'configs' => [
        [
            'field_name' => 'theme',                    // 字段名
            'description' => '这是一个字段主题',           // 描述
            'field_type' => 'select',                   // 字段类型: select,input,switch
            'select_options' => [                       // [filed_type]_options
                '奶绿'
            ]
        ]
    ]
];
