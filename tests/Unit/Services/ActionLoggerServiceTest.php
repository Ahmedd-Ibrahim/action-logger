<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Processors\BatchActionProcessor;
use BIM\ActionLogger\Processors\ProcessorFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Mockery;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Facades\Activity as ActivityLog;
use Tests\Models\User;

class ActionLoggerServiceTest extends TestCase
{
    protected ActionLoggerService $service;
    protected ProcessorFactory $processorFactory;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a real ProcessorFactory
        $this->processorFactory = new ProcessorFactory();
        
        // Create service with real factory
        $this->service = new ActionLoggerService($this->processorFactory);
    }
    
    /** @test */
    public function it_starts_and_commits_batch_with_type()
    {
        $batchType = 'test-batch';
        
        // Start batch and capture UUID
        $this->service->startBatch(null, ['type' => $batchType]);
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('currentBatchUuid');
        $property->setAccessible(true);
        $batchUuid = $property->getValue($this->service);
        
        // Verify batch was started
        $this->assertNotNull($batchUuid);
        $this->assertIsString($batchUuid);
        
        // Create some activities in this batch
        $activities = $this->createBatchActivities(3, $batchUuid);
        
        // Commit batch
        $result = $this->service->commitBatch();
        
        // Verify result
        $this->assertTrue($result);
        
        // Verify batch was cleared
        $this->assertNull($property->getValue($this->service));
    }
    
    /** @test */
    public function it_resolves_processor_from_route_information()
    {
        // Create a custom processor class for testing
        $customProcessorClass = BatchActionProcessor::class;
        
        // Configure route processors
        Config::set('action-logger.route_processors', [
            'test.route' => $customProcessorClass
        ]);
        
        // Create test activity directly
        $activity = new Activity();
        $activity->log_name = 'default';
        $activity->description = 'Test activity';
        $activity->subject_type = User::class;
        $activity->subject_id = 1;
        $activity->event = 'created';
        $activity->properties = ['batch_metadata' => ['name' => 'test.route']];
        $activity->save();
        
        // Process the activity
        $result = $this->service->process(collect([$activity]));
        
        // Verify we got a result
        $this->assertIsArray($result);
    }
    
    /** @test */
    public function it_discards_batch_correctly()
    {
        // Start a batch
        $batchUuid = $this->service->startBatch();
        
        // Create activities
        $activities = $this->createBatchActivities(2, $batchUuid);
        
        // Discard batch (default is not to delete activities)
        $result = $this->service->discardBatch();
        
        // Verify batch was cleared
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('currentBatchUuid');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->service));
        
        // Activities should still exist
        $this->assertEquals(2, Activity::where('batch_uuid', $batchUuid)->count());
        
        // Now configure to delete discarded activities
        Config::set('action-logger.batch.delete_discarded', true);
        
        // Start a new batch
        $batchUuid = $this->service->startBatch();
        
        // Create activities
        $activities = $this->createBatchActivities(2, $batchUuid);
        
        // Discard batch (should delete activities)
        $result = $this->service->discardBatch();
        
        // Verify activities were deleted
        $this->assertEquals(0, Activity::where('batch_uuid', $batchUuid)->count());
    }
} 