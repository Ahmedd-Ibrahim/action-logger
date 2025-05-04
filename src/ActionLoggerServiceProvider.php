<?php

namespace BIM\ActionLogger;

use Illuminate\Support\ServiceProvider;
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Processors\ProcessorFactory;
use BIM\ActionLogger\Middleware\AutoBatchLoggingMiddleware;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Illuminate\Routing\Router;

class ActionLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Spatie's activity log service provider
        $this->app->register(ActivitylogServiceProvider::class);

        $this->mergeConfigFrom(__DIR__.'/../config/action-logger.php', 'action-logger');

        $this->app->singleton(ProcessorFactory::class, function ($app) {
            return new ProcessorFactory();
        });

        $this->app->singleton('action-logger', function ($app) {
            return new ActionLoggerService($app->make(ProcessorFactory::class));
        });

        $this->app->alias('action-logger', ActionLoggerService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/action-logger.php' => config_path('action-logger.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/action-logger'),
        ], 'lang');

        $this->loadTranslationsFrom(__DIR__.'/Resources/lang', 'action-logger');

        $this->registerMiddleware();
    }

    /**
     * Register middleware based on configuration
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $middleware = AutoBatchLoggingMiddleware::class;
        
        $middlewareConfig = config('action-logger.auto_batch_middleware');
        
        if ($middlewareConfig === true) {
            $router->pushMiddlewareToGroup('web', $middleware);
        } elseif (is_array($middlewareConfig)) {
            foreach ($middlewareConfig as $group) {
                $router->pushMiddlewareToGroup($group, $middleware);
            }
        }
        
        $router->aliasMiddleware('auto-batch-logging', $middleware);
    }
}