<?php

namespace Tests\Unit\Processors;

use BIM\ActionLogger\Processors\BatchActionProcessor;
use BIM\ActionLogger\Processors\ProcessorFactory;
use BIM\ActionLogger\Services\ActionLoggerService;
use Illuminate\Support\Facades\Config;
use Mockery;
use ReflectionClass;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
use Illuminate\Support\Collection;

class ProcessorResolutionTest extends TestCase
{
    protected ActionLoggerService $actionLogger;
    protected ProcessorFactory $processorFactory;

    protected function setUp(): void
    {
        parent::setUp();
        
        // We need a custom configuration for these tests
        $this->resetProcessorConfig();
    }
    
    protected function tearDown(): void
    {
        $this->resetProcessorConfig();
        parent::tearDown();
    }
    
    /**
     * Reset processor configuration
     */
    protected function resetProcessorConfig(): void
    {
        Config::set('action-logger.route_processors', []);
        Config::set('action-logger.controller_processors', []);
        Config::set('action-logger.default_processors', [
            'default' => BatchActionProcessor::class
        ]);
    }
    
    /**
     * Access protected method resolveProcessorFromRoute
     */
    protected function callResolveProcessorFromRoute(Activity $activity): ?string
    {
        $reflection = new ReflectionClass(ActionLoggerService::class);
        $method = $reflection->getMethod('resolveProcessorFromRoute');
        $method->setAccessible(true);
        return $method->invoke($this->actionLogger, $activity);
    }
    
    /**
     * Access protected method getProcessorForActivities
     */
    protected function callGetProcessorForActivities($activities)
    {
        $reflection = new ReflectionClass(ActionLoggerService::class);
        $method = $reflection->getMethod('getProcessorForActivities');
        $method->setAccessible(true);
        return $method->invoke($this->actionLogger, $activities);
    }

    /** @test */
    public function it_resolves_processor_from_exact_route_name_match()
    {
        // Configure processors
        Config::set('action-logger.route_processors', [
            'user.create' => 'App\Processors\UserCreateProcessor',
        ]);
        
        // Create activity with route metadata
        $activity = $this->createActivity([
            'properties' => ['batch_metadata' => [
                'name' => 'user.create',
            ]]
        ]);
        
        // Resolve processor
        $processor = $this->callResolveProcessorFromRoute($activity);
        
        // Assert result
        $this->assertEquals('App\Processors\UserCreateProcessor', $processor);
    }

    /** @test */
    public function it_resolves_processor_from_wildcard_route_name_match()
    {
        // Configure processors
        Config::set('action-logger.route_processors', [
            'user.*' => 'App\Processors\UserProcessor',
            'admin.*' => 'App\Processors\AdminProcessor',
        ]);
        
        // Create activity with route metadata
        $activity = $this->createActivity([
            'properties' => ['batch_metadata' => [
                'name' => 'user.profile.edit',
            ]]
        ]);
        
        // Resolve processor
        $processor = $this->callResolveProcessorFromRoute($activity);
        
        // Assert result
        $this->assertEquals('App\Processors\UserProcessor', $processor);
    }

    /** @test */
    public function it_resolves_processor_from_controller_action()
    {
        // Configure processors
        Config::set('action-logger.controller_processors', [
            'App\Http\Controllers\ProductController@destroy' => 'App\Processors\ProductDeleteProcessor',
        ]);
        
        // Create activity with controller metadata
        $activity = $this->createActivity([
            'properties' => ['batch_metadata' => [
                'controller' => 'App\Http\Controllers\ProductController',
                'action' => 'destroy',
            ]]
        ]);
        
        // Resolve processor
        $processor = $this->callResolveProcessorFromRoute($activity);
        
        // Assert result
        $this->assertEquals('App\Processors\ProductDeleteProcessor', $processor);
    }

    /** @test */
    public function it_falls_back_to_default_processor_if_no_route_match()
    {
        // Clear all processor configurations
        $this->resetProcessorConfig();
        
        // Set default processor
        Config::set('action-logger.default_processors.default', BatchActionProcessor::class);
        
        // Create activity with no matching configuration
        $activity = $this->createActivity([
            'subject_type' => 'App\Models\Unknown',
            'event' => 'unknown',
        ]);
        
        // Get collection of activities
        $activities = collect([$activity]);
        
        // Get processor for activities
        $processor = $this->callGetProcessorForActivities($activities);
        
        // Verify it's the default processor
        $this->assertInstanceOf(BatchActionProcessor::class, $processor);
    }
} 