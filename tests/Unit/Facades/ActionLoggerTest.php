<?php

namespace Tests\Unit\Facades;

use Tests\TestCase;
use Tests\Models\User;
use Tests\Models\Post;
use BIM\ActionLogger\Facades\ActionLogger;
use BIM\ActionLogger\Enums\Action;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionLoggerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_log_actions_using_facade()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        ActionLogger::on($post)
            ->by($user)
            ->withProperties(['key' => 'value'])
            ->withLogName('custom_log')
            ->log(Action::CREATED);

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
    public function it_can_log_with_custom_description_using_facade()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        ActionLogger::on($post)
            ->by($user)
            ->withDescription('Custom description')
            ->log(Action::CREATED);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Custom description',
        ]);
    }
} 