<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Models\User;
use Tests\Models\Post;
use BIM\ActionLogger\Enums\Action;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_log_actions_using_helper_function()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        log_action(
            $post,
            Action::CREATED,
            $user,
            ['key' => 'value'],
            'custom_log'
        );

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'description' => Action::CREATED->value(),
            'log_name' => 'custom_log',
        ]);

        $activity = Activity::first();
        $this->assertEquals(['key' => 'value'], $activity->properties->toArray());
    }

    /** @test */
    public function it_can_manage_batch_logging_using_helpers()
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create();

        start_batch();
        $batchUuid = get_batch_uuid();

        foreach ($posts as $post) {
            log_action($post, Action::UPDATED, $user);
        }

        end_batch();

        $activities = Activity::forBatch($batchUuid)->get();
        $this->assertCount(3, $activities);
        $this->assertEquals($batchUuid, $activities->first()->batch_uuid);
    }

    /** @test */
    public function it_can_get_action_logs_using_helper()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        log_action($post, Action::CREATED, $user);

        $logs = get_action_logs();
        $this->assertCount(1, $logs);

        $logs = get_action_logs(function($query) {
            return $query->where('log_name', 'default');
        });
        $this->assertCount(1, $logs);
    }

    /** @test */
    public function it_can_execute_within_batch_using_helper()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        within_batch(function($uuid) use ($user, $post) {
            log_action($post, Action::UPDATED, $user);
            $this->assertTrue(is_batch_open());
            $this->assertEquals($uuid, get_batch_uuid());
        });

        $this->assertFalse(is_batch_open());
        $this->assertNull(get_batch_uuid());

        $activity = Activity::first();
        $this->assertNotNull($activity->batch_uuid);
    }
} 