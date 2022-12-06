<?php

return [
    'name' => 'V2board',
    'description' => 'V2board default theme',
    'version' => '1.5.6',
    'images' => 'https://images.unsplash.com/photo-1515405295579-ba7b45403062?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2160&q=80',
    'configs' => [
        [
            'label' => 'Theme Color',                               // Tags
            'placeholder' => 'Please select theme color',                   // Description
            'field_name' => 'theme_color',                    // Field name Used as data key
            'field_type' => 'select',                         // Field Type: select,input,switch
            'select_options' => [                             // Valid when the field type is select
                'default' => 'Default (Blue)',
                'green' => 'Milk Green',
                'black' => 'Black',
                'darkblue' => 'Dark Blue',
            ],
            'default_value' => 'default'                       // The default value of the field will be initialized for the first time
        ], [
            'label' => 'Background',
            'placeholder' => 'Please enter the background image URL',
            'field_name' => 'background_url',
            'field_type' => 'input'
        ], [
            'label' => 'Sidebar style',
            'placeholder' => 'Please select sidebar style',
            'field_name' => 'theme_sidebar',
            'field_type' => 'select',
            'select_options' => [
                'light' => 'Bright',
                'dark' => 'dark'
            ],
            'default_value' => 'light'
        ], [
            'label' => 'Top Style',
            'placeholder' => 'Please select the top style',
            'field_name' => 'theme_header',
            'field_type' => 'select',
            'select_options' => [
                'light' => 'Bright',
                'dark' => 'dark'
            ],
            'default_value' => 'dark'
        ], [
            'label' => 'Custom footer HTML',
            'placeholder' => 'Customer service JS code can be added, etc.',
            'field_name' => 'custom_html',
            'field_type' => 'textarea'
        ]
    ]
];
