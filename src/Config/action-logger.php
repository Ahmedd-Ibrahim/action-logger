<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Processors
    |--------------------------------------------------------------------------
    |
    | This is the default processor class that will be used when no specific
    | processor is found for an activity.
    |
    */
    'default_processors' => [
        'default' => \BIM\ActionLogger\Processors\BatchActionProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Processors
    |--------------------------------------------------------------------------
    |
    | Here you may define processors for specific routes.
    | The key should be the route name or pattern, and the value should be
    | the fully qualified class name of the processor.
    |
    | Example:
    | 'users.create' => \App\Processors\UserCreateProcessor::class,
    | 'users.*' => \App\Processors\UserProcessor::class,
    |
    */
    'route_processors' => [
        // 'users.create' => \App\Processors\UserCreateProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Controller Processors
    |--------------------------------------------------------------------------
    |
    | Here you may define processors for specific controller actions.
    | The key should be the controller@action, and the value should be
    | the fully qualified class name of the processor.
    |
    | Example:
    | 'App\Http\Controllers\UserController@store' => \App\Processors\UserCreateProcessor::class,
    |
    */
    'controller_processors' => [
        // 'App\Http\Controllers\UserController@store' => \App\Processors\UserCreateProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure batch logging behavior.
    |
    */
    'batch' => [
        'delete_discarded' => false,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should be excluded from automatic batch logging.
    |
    */
    'excluded_routes' => [
        'horizon*',
        'telescope*',
        'debugbar*',
    ],
]; 