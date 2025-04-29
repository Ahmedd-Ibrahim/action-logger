<?php

namespace BIM\ActionLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Http\Kernel;
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Processors\ProcessorFactory;
use BIM\ActionLogger\Facades\ActionLogger;
use BIM\ActionLogger\Middleware\AutoBatchLoggingMiddleware;

class ActionLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/action-logger.php', 
            'action-logger'
        );

        $this->app->singleton(ProcessorFactory::class);
        
        $this->app->singleton('action-logger', function ($app) {
            return new ActionLoggerService($app->make(ProcessorFactory::class));
        });

        $this->app->alias('action-logger', ActionLogger::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (class_exists(AutoBatchLoggingMiddleware::class)) {
            $this->registerMiddleware(
                AutoBatchLoggingMiddleware::class,
                config('action-logger.auto_batch_middleware')
            );
        }

        $this->publishes([
            __DIR__.'/../config/action-logger.php' => config_path('action-logger.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'action-logger');
    }

    /**
     * Register middleware based on configuration
     */
    protected function registerMiddleware(string $middlewareClass, $registerType): void
    {
        if ($registerType === false) {
            return;
        }

        if ($registerType === true) {
            $this->app->make(Kernel::class)->pushMiddleware($middlewareClass);
            return;
        }

        foreach (Arr::wrap($registerType) as $group) {
            $this->app['router']->pushMiddlewareToGroup($group, $middlewareClass);
        }
    }
}