<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Tests\Models\User;
use Tests\Models\Post;
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Enums\Action;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionLoggerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ActionLoggerService $actionLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actionLogger = app(ActionLoggerService::class);
    }

    /** @test */
    public function it_can_log_a_single_action()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actionLogger
            ->on($post)
            ->by($user)
            ->log(Action::CREATED);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'description' => Action::CREATED->value(),
        ]);
    }

    /** @test */
    public function it_can_log_with_custom_properties()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actionLogger
            ->on($post)
            ->by($user)
            ->withProperties(['key' => 'value'])
            ->log(Action::CREATED);

        $activity = Activity::first();
        $this->assertEquals(['key' => 'value'], $activity->properties->toArray());
    }

    /** @test */
    public function it_can_log_with_custom_log_name()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actionLogger
            ->on($post)
            ->by($user)
            ->withLogName('custom_log')
            ->log(Action::CREATED);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'custom_log',
        ]);
    }

    /** @test */
    public function it_can_log_with_custom_description()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actionLogger
            ->on($post)
            ->by($user)
            ->withDescription('Custom description')
            ->log(Action::CREATED);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Custom description',
        ]);
    }
} 