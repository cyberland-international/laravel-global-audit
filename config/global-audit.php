<?php
return [
    'enabled' => true,

    'models_namespace' => 'App\\Models\\',

    'hidden' => [
        'password',
        'remember_token',
        'api_token',
        'token',
    ],

    'skip_models' => [
        // e.g. \App\Models\AuditTrail::class,
    ],

    'middleware' => [
        'enabled' => true,
        'only' => [
            // 'api/*',
        ],
        'except' => [
            'telescope*',
            'pulse*',
            'health*',
            'up*',
            'api/me',
        ],
    ],
];