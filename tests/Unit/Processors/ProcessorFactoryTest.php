<?php

namespace Tests\Unit\Processors;

use Tests\TestCase;
use Tests\Models\User;
use Tests\Models\Post;
use BIM\ActionLogger\Processors\ProcessorFactory;
use BIM\ActionLogger\Processors\BatchActionProcessor;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class ProcessorFactoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProcessorFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = app(ProcessorFactory::class);
    }

    /** @test */
    public function it_returns_batch_processor_for_batch_activities()
    {
        $batchUuid = 'test-batch-uuid';
        
        Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'batch_uuid' => $batchUuid,
            'properties' => ['attributes' => ['title' => 'Test Post']],
        ]);

        $activities = Activity::where('batch_uuid', $batchUuid)->get();
        $processor = $this->factory->getProcessor($activities);

        $this->assertInstanceOf(BatchActionProcessor::class, $processor);
    }

    /** @test */
    public function it_returns_batch_processor_for_empty_collection()
    {
        $processor = $this->factory->getProcessor(collect());
        $this->assertInstanceOf(BatchActionProcessor::class, $processor);
    }

    /** @test */
    public function it_returns_batch_processor_for_single_activity()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'properties' => ['attributes' => ['title' => 'Test Post']],
        ]);

        $activities = Activity::all();
        $processor = $this->factory->getProcessor($activities);

        $this->assertInstanceOf(BatchActionProcessor::class, $processor);
    }

    /** @test */
    public function it_returns_batch_processor_for_multiple_activities()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        // Create multiple activities
        Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'properties' => ['attributes' => ['title' => 'Test Post']],
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'updated',
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'properties' => [
                'attributes' => ['title' => 'Updated Post'],
                'old' => ['title' => 'Test Post']
            ],
        ]);

        $activities = Activity::all();
        $processor = $this->factory->getProcessor($activities);

        $this->assertInstanceOf(BatchActionProcessor::class, $processor);
    }

    /** @test */
    public function it_resolves_processor_from_route_name()
    {
        // Define test processor class
        $testProcessorClass = BatchActionProcessor::class;

        // Configure route processors
        Config::set('action-logger.route_processors', [
            'users.create' => $testProcessorClass
        ]);
        
        // Create activity with route metadata
        $activity = $this->createActivity([
            'properties' => ['batch_metadata' => [
                'name' => 'users.create',
            ]]
        ]);
        
        // Test processor resolution
        $processor = $this->factory->getProcessor($activity);
        $this->assertInstanceOf($testProcessorClass, $processor);
    }

    /** @test */
    public function it_resolves_processor_from_wildcard_route_name()
    {
        // Define test processor class
        $testProcessorClass = BatchActionProcessor::class;

        // Configure route processors with wildcard
        Config::set('action-logger.route_processors', [
            'users.*' => $testProcessorClass
        ]);
        
        // Create activity with route metadata
        $activity = $this->createActivity([
            'properties' => ['batch_metadata' => [
                'name' => 'users.edit',
            ]]
        ]);
        
        // Test processor resolution
        $processor = $this->factory->getProcessor($activity);
        $this->assertInstanceOf($testProcessorClass, $processor);
    }

    /** @test */
    public function it_resolves_processor_from_controller_action()
    {
        // Define test processor class
        $testProcessorClass = BatchActionProcessor::class;

        // Configure controller processors
        Config::set('action-logger.controller_processors', [
            'App\Http\Controllers\UserController@update' => $testProcessorClass
        ]);
        
        // Create activity with controller metadata
        $activity = $this->createActivity([
            'properties' => ['batch_metadata' => [
                'controller' => 'App\Http\Controllers\UserController',
                'action' => 'update',
            ]]
        ]);
        
        // Test processor resolution
        $processor = $this->factory->getProcessor($activity);
        $this->assertInstanceOf($testProcessorClass, $processor);
    }

    /** @test */
    public function it_falls_back_to_default_processor_if_no_match()
    {
        // Create activity with no matching route information
        $activity = $this->createActivity([
            'properties' => ['batch_metadata' => [
                'name' => 'non.existent.route',
            ]]
        ]);
        
        // Test processor resolution
        $processor = $this->factory->getProcessor($activity);
        $this->assertInstanceOf(BatchActionProcessor::class, $processor);
    }

    /** @test */
    public function it_handles_missing_batch_metadata()
    {
        // Create activity with no batch metadata
        $activity = $this->createActivity();
        
        // Test processor resolution
        $processor = $this->factory->getProcessor($activity);
        $this->assertInstanceOf(BatchActionProcessor::class, $processor);
    }
} 