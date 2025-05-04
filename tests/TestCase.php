<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\ActivitylogServiceProvider;
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Processors\ProcessorFactory;
use BIM\ActionLogger\ActionLoggerServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected ActionLoggerService $actionLogger;
    protected ProcessorFactory $processorFactory;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure the package
        Config::set('activitylog.table_name', 'activity_log');
        Config::set('activitylog.database_connection', 'testing');
        
        // Run migrations
        $this->runMigrations();
        
        // Create service instances
        $this->processorFactory = app(ProcessorFactory::class);
        $this->actionLogger = app(ActionLoggerService::class);
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
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Load package configuration
        $app['config']->set('action-logger', require __DIR__.'/../src/Config/action-logger.php');
    }

    protected function runMigrations(): void
    {
        // Get the migrations path
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->artisan('migrate')->run();
    }

    protected function createActivity(array $attributes = []): Activity
    {
        return Activity::create(array_merge([
            'log_name' => 'default',
            'description' => 'Test activity',
            'subject_type' => 'App\Models\User',
            'subject_id' => 1,
            'causer_type' => 'App\Models\User',
            'causer_id' => 1,
            'properties' => ['attributes' => []],
            'batch_uuid' => null,
        ], $attributes));
    }

    protected function createBatchActivities(int $count, string $batchUuid, array $attributes = []): Collection
    {
        $activities = collect();
        for ($i = 0; $i < $count; $i++) {
            $activities->push($this->createActivity(array_merge([
                'batch_uuid' => $batchUuid,
                'description' => 'Test activity ' . ($i + 1),
            ], $attributes)));
        }
        return $activities;
    }

    protected function assertBatchDescription(string $expected, array $actual): void
    {
        $this->assertArrayHasKey('description', $actual);
        $this->assertEquals($expected, $actual['description']);
    }

    protected function assertBatchChanges(array $expected, array $actual): void
    {
        $this->assertArrayHasKey('changes', $actual);
        $this->assertEquals($expected, $actual['changes']);
    }

    protected function assertBatchType(string $expected, array $actual): void
    {
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals($expected, $actual['type']);
    }
} 