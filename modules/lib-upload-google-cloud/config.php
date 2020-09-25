<?php

return [
    '__name' => 'lib-upload-google-cloud',
    '__version' => '0.1.0',
    '__git' => 'git@github.com:getmim/lib-upload-google-cloud.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-upload-google-cloud' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-google-cloud' => NULL
            ],
            [
                'lib-upload' => NULL
            ],
            [
                'lib-image' => NULL
            ],
            [
                'lib-curl' => NULL 
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'LibUploadGoogleCloud\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-upload-google-cloud/library'
            ]
        ],
        'files' => []
    ],
    'libUpload' => [
        'keeper' => [
            'handlers' => [
                'google-cloud' => [
                    'use' => true,
                    'class' => 'LibUploadGoogleCloud\\Library\\Keeper'
                ]
            ]
        ]
    ],
    'libMedia' => [
        'handlers' => [
            'google-cloud' => 'LibUploadGoogleCloud\\Library\\Handler'
        ]
    ]
];
