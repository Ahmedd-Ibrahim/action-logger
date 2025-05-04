<?php

namespace Tests\Unit\Processors;

use BIM\ActionLogger\Processors\BatchActionProcessor;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class BatchActionProcessorTest extends TestCase
{
    /** @test */
    public function it_includes_all_entities_with_different_actions()
    {
        // Create test activities with different actions
        $activities = new Collection([
            $this->createTestActivity('created', 'App\Models\User', 1),
            $this->createTestActivity('updated', 'App\Models\Post', 1),
            $this->createTestActivity('deleted', 'App\Models\Comment', 1),
            $this->createTestActivity('column_updated', 'App\Models\Article', 1)
        ]);
        
        // Process activities
        $processor = new BatchActionProcessor($activities);
        $result = $processor->process();
        
        // Get first batch result
        $batchResult = $result[0] ?? [];
        
        // Check that all entities are included
        $this->assertArrayHasKey('entities', $batchResult);
        $entities = $batchResult['entities'];
        
        // Should have 4 entities
        $this->assertCount(4, $entities);
        
        // Check that each entity has the correct action
        $actions = array_column($entities, 'action');
        $this->assertContains('created', $actions);
        $this->assertContains('updated', $actions);
        $this->assertContains('deleted', $actions);
        $this->assertContains('column_updated', $actions);
        
        // Each entity should have an id and type
        foreach ($entities as $entity) {
            $this->assertArrayHasKey('id', $entity);
            $this->assertArrayHasKey('type', $entity);
            $this->assertArrayHasKey('action', $entity);
            $this->assertArrayHasKey('changes', $entity);
        }
    }
    
    /**
     * Create a test activity
     */
    private function createTestActivity(string $event, string $subjectType, int $subjectId): Activity
    {
        return new Activity([
            'log_name' => 'default',
            'description' => $event,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'event' => $event,
            'batch_uuid' => 'test-batch-uuid',
            'properties' => [
                'attributes' => ['name' => 'New value'],
                'old' => ['name' => 'Old value']
            ]
        ]);
    }
} 