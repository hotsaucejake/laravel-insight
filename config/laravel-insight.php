<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Model Namespace(s)
    |--------------------------------------------------------------------------
    |
    | Here you may specify one or more namespaces or directories where your
    | Eloquent models reside. This will be used to list available models.
    |
    */
    'model_namespaces' => [
        'App\\Models',
        'App', // You can include more namespaces as needed.
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Duration
    |--------------------------------------------------------------------------
    |
    | This option controls how long (in minutes) the output from the model:show
    | command should be cached before being refreshed.
    |
    */
    'cache_duration' => 60,

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | You may disable or enable specific insight features here.
    | For now, only the model:show feature is implemented.
    |
    */
    'features' => [
        'model_show' => true,
    ],
];
