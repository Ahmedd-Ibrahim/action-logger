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
    | The class that will be used for action enums. This class must implement
    | the BIM\ActionLogger\Contracts\ActionInterface interface.
    |
    */
    'action_class' => \BIM\ActionLogger\Enums\Action::class,

    /*
    |--------------------------------------------------------------------------
    | Custom Processors
    |--------------------------------------------------------------------------
    |
    | Here you may define custom processors for specific actions.
    | The key is the action name and the value is the fully qualified class name.
    |
    */
    'custom_processors' => [
        // Example:
        // 'approve' => \App\Processors\ApproveActionProcessor::class,
        // 'reject' => \App\Processors\RejectActionProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Actions
    |--------------------------------------------------------------------------
    |
    | Here you may define custom actions that can be used in the logger.
    | The key is the action name and the value is the translation key.
    |
    */
    'custom_actions' => [
        'approve' => 'approved',
        'reject' => 'rejected',
        'accept' => 'accepted',
    ],

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
    | - true: Register as global middleware
    | - array: Register for specific middleware groups (e.g. ['web', 'api'])
    |
    */
    'auto_batch_middleware' => true,

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Here you may define routes that should be excluded from batch logging.
    | You can use route patterns as defined in Laravel's route matching.
    |
    */
    'excluded_routes' => [
        // Example:
        // 'api/health',
        // 'admin/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Name Resolver
    |--------------------------------------------------------------------------
    |
    | Here you may define a callback that will be used to generate batch names.
    | The callback will receive the current request as its only argument.
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
    | Attribute Formatters
    |--------------------------------------------------------------------------
    |
    | Here you may define custom formatters for specific attributes.
    | The key is the attribute name and the value is the formatter class.
    |
    */
    'attribute_formatters' => [
        // Example:
        // 'status' => \App\Formatters\StatusFormatter::class,
        // 'date' => \App\Formatters\DateFormatter::class,
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
]; 