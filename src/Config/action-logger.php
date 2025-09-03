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

    /*
    |--------------------------------------------------------------------------
    | Log Headers
    |--------------------------------------------------------------------------
    |
    | Whether to log HTTP headers in request tracking.
    | Set to true to include headers in the logged request data.
    |
    */
    'log_headers' => env('ACTIVITY_LOGGER_LOG_HEADERS', false),

    /*
    |--------------------------------------------------------------------------
    | Sensitive Headers
    |--------------------------------------------------------------------------
    |
    | Headers that should be redacted when logging.
    | These headers will be replaced with '[REDACTED]' in the logs.
    |
    */
    'sensitive_headers' => [
        'authorization',
        'cookie',
        'x-api-key',
        'x-auth-token',
        'x-csrf-token',
        'x-session-token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Routes
    |--------------------------------------------------------------------------
    |
    | Routes that contain sensitive data and should not log request data.
    | When a route matches these patterns, only 'sensitive_data: true' will be logged.
    |
    */
    'sensitive_routes' => [
        'auth/login',
        'auth/register',
        'password/reset',
        'password/confirm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Request Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should be excluded from request data logging.
    | These fields will be filtered out from the logged request data.
    |
    */
    'excluded_request_fields' => [
        'password',
        'password_confirmation',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'secret',
        'private_key',
        'credit_card',
        'ssn',
        'social_security_number',
    ],
];
