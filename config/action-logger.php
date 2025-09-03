<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Action Logger Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the action logger package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Log Name
    |--------------------------------------------------------------------------
    |
    | This is the default log name that will be used when logging actions.
    |
    */
    'default_log_name' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Default Log Description
    |--------------------------------------------------------------------------
    |
    | This is the default log description that will be used when logging actions.
    |
    */
    'default_log_description' => 'Action performed',

    /*
    |--------------------------------------------------------------------------
    | Subject Type
    |--------------------------------------------------------------------------
    |
    | This is the default subject type that will be used when logging actions.
    |
    */
    'subject_type' => null,

    /*
    |--------------------------------------------------------------------------
    | Causer Type
    |--------------------------------------------------------------------------
    |
    | This is the default causer type that will be used when logging actions.
    |
    */
    'causer_type' => null,

    /*
    |--------------------------------------------------------------------------
    | Action Class
    |--------------------------------------------------------------------------
    |
    | This is the class that contains the enum of standard actions.
    | You can extend this with your own implementation.
    |
    */
    'action_class' => \BIM\ActionLogger\Enums\Action::class,

    /*
    |--------------------------------------------------------------------------
    | Processor Resolution
    |--------------------------------------------------------------------------
    |
    | Configure how processors are resolved based on subject type and action.
    |
    | Dynamic processors are automatically resolved using the format:
    | '{model}.{action}' where:
    | - {model} is the snake_case base name of the subject class (e.g., 'rent_request')
    | - {action} is the action value (e.g., 'created')
    |
    | Examples:
    | - Subject: App\Models\RentRequest, Action: created = 'rent_request.created'
    | - Subject: App\Models\User, Action: updated = 'user.updated'
    |
    */
    'processor_resolution' => [
        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        |
        | The base namespace where processors are located.
        | The system will look for processors in this namespace.
        |
        */
        'namespace' => 'App\\Processors',

        /*
        |--------------------------------------------------------------------------
        | Suffix
        |--------------------------------------------------------------------------
        |
        | The suffix to append to processor class names.
        |
        */
        'suffix' => 'Processor',

        /*
        |--------------------------------------------------------------------------
        | Fallback Strategy
        |--------------------------------------------------------------------------
        |
        | When a specific processor is not found, the system will try fallbacks:
        | - 'model': Try a general processor for the model (e.g., 'rent_request.*')
        | - 'action': Try a general processor for the action (e.g., '*.created')
        | - 'none': Don't use fallbacks
        |
        */
        'fallback_strategy' => 'model',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Processors
    |--------------------------------------------------------------------------
    |
    | Here you may explicitly define custom processors for specific subject-action
    | combinations instead of relying on dynamic resolution.
    |
    | The key format is '{subject_type}.{action}' and the value is the
    | fully qualified class name of the processor.
    |
    | Example:
    | 'custom_processors' => [
    |     'rent_request.created' => \App\Processors\RentRequestProcessor::class,
    |     'user.updated' => \App\Processors\UserUpdatedProcessor::class,
    | ],
    |
    */
    'custom_processors' => [],

    /*
    |--------------------------------------------------------------------------
    | Default Processor
    |--------------------------------------------------------------------------
    |
    | The default processor to use when no specific processor is found
    | for a subject-action combination.
    |
    */
    'default_processor' => \BIM\ActionLogger\Processors\BatchActionProcessor::class,

    /*
    |--------------------------------------------------------------------------
    | Model Translations
    |--------------------------------------------------------------------------
    |
    | Here you may define translations for your model names.
    | The key is the model class name and the value is the translation key.
    |
    */
    'model_translations' => [
        // Example:
        // 'App\Models\User' => 'user',
        // 'App\Models\Post' => 'post',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Batch Logging Middleware
    |--------------------------------------------------------------------------
    |
    | This option controls how the auto batch logging middleware is registered.
    | You can set it to:
    | - false: Don't register the middleware
    | - true: Register as global middleware for the web group
    | - array: Register for specific middleware groups (e.g. ['web', 'api'])
    |
    */
    'auto_batch_middleware' => true,

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should be excluded from automatic batch logging.
    | You can use patterns like 'admin/*' to exclude entire route groups.
    |
    */
    'excluded_routes' => [
        'horizon*',
        'telescope*',
        '_debugbar*',
        '_ignition*',
        'api/health',
        'livewire*',
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
    'log_headers' => env('ACTIVITY_LOGGER_LOG_HEADERS', true),

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

    /*
    |--------------------------------------------------------------------------
    | Batch Name Resolver
    |--------------------------------------------------------------------------
    |
    | A custom callback to generate batch names based on the request.
    | This function receives the request object and should return a string.
    |
    | Example:
    | 'batch_name_resolver' => function($request) {
    |     return $request->user()->id . '_' . $request->method() . '_' . $request->path();
    | }
    |
    */
    'batch_name_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Message Templates
    |--------------------------------------------------------------------------
    |
    | Here you may define custom message templates for different actions.
    | The key is the action name and the value is the template.
    | Available placeholders:
    | - {entity}: The entity name
    | - {id}: The entity ID
    | - {changes}: The changes made
    |
    */
    'message_templates' => [
        'created' => '{entity} #{id} has been created with {changes}',
        'updated' => '{entity} #{id} has been updated with {changes}',
        'deleted' => '{entity} #{id} has been deleted',
    ],

    /*
    |--------------------------------------------------------------------------
    | Entity Resolvers
    |--------------------------------------------------------------------------
    |
    | Here you may define custom resolvers for specific entity types.
    | The key is the entity type and the value is the resolver class.
    |
    */
    'entity_resolvers' => [
        // Example:
        // 'App\Models\User' => \App\Resolvers\UserResolver::class,
        // 'App\Models\Post' => \App\Resolvers\PostResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | This is the database connection you want to use to store the activity logs.
    |
    */
    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | This is the table name you want to use to store the activity logs.
    |
    */
    'table_name' => 'activity_log',

    /*
    |--------------------------------------------------------------------------
    | Batch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the batch processing behavior.
    |
    */
    'batch' => [
        // Enable batch processing
        'enabled' => true,

        // Automatically start batch on middleware
        'auto_start' => true,

        // Automatically end batch on middleware
        'auto_end' => true,

        // Delete activities when a batch is discarded
        'delete_discarded' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Processors
    |--------------------------------------------------------------------------
    |
    | Configure which processor to use for each model and action combination.
    | Format: 'Model::class' => ['action' => ProcessorClass::class]
    |
    */
    'processors' => [
        // Example:
        // 'App\\Models\\RentRequest' => [
        //     'updated' => \BIM\ActionLogger\Processors\RentRequestProcessor::class,
        //     'approved' => \BIM\ActionLogger\Processors\RentRequestProcessor::class,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Processors
    |--------------------------------------------------------------------------
    |
    | Fallback processors when no specific processor is found.
    |
    */
    'default_processors' => [
        'default' => \BIM\ActionLogger\Processors\BatchActionProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Attribute Formatters
    |--------------------------------------------------------------------------
    |
    | Custom formatters for specific attribute types.
    | Format: 'attribute_name' => FormatterClass::class
    |
    */
    'attribute_formatters' => [
        // Example:
        // 'amount' => \App\Formatters\AmountFormatter::class,
        // 'date' => \App\Formatters\DateFormatter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    |
    | Configure how translations are handled.
    |
    */
    'translations' => [
        // Fallback to validation.attributes for attribute translations
        'use_validation_attributes' => true,

        // Translation key prefixes
        'action_prefix' => 'activity.actions',
        'model_prefix' => 'models',
        'attribute_prefix' => 'attributes',
    ],
];
