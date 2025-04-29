<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use BIM\ActionLogger\ActionLoggerServiceProvider;
use BIM\ActionLogger\Services\LogBatch;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Tests\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        // Set up test environment
        $this->app['config']->set('database.default', 'testbench');
        $this->app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set up auth configuration
        $this->app['config']->set('auth.defaults.guard', 'web');
        $this->app['config']->set('auth.guards.web', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            ActivitylogServiceProvider::class,
            ActionLoggerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set up test environment
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set up auth configuration
        $app['config']->set('auth.defaults.guard', 'web');
        $app['config']->set('auth.guards.web', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        // Set up activity log configuration
        $app['config']->set('activitylog.enabled', true);
        $app['config']->set('activitylog.default_log_name', 'default');
        $app['config']->set('activitylog.default_causer_type', 'App\Models\User');
    }
} 