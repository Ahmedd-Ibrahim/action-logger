<?php

namespace Tests\Unit;

use BIM\ActionLogger\Facades\ActionLogger;
use Tests\TestCase;
use Tests\Models\User;
use Tests\Models\Post;
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Enums\Action;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MacrosTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_add_custom_action_methods()
    {
        ActionLoggerService::macro('approve', function (array $extraProperties = []) {
            return $this->log(Action::UPDATED, array_merge(['status' => 'approved'], $extraProperties));
        });

        $user = User::factory()->create();
        $post = Post::factory()->create();

        ActionLogger::on($post)
            ->by($user)
            ->approve(['reason' => 'Approved by manager']);

        $activity = Activity::first();
        $this->assertEquals(Action::UPDATED->value(), $activity->description);
        $this->assertEquals(['status' => 'approved', 'reason' => 'Approved by manager'], $activity->properties->toArray());
    }

    /** @test */
    public function it_can_add_multiple_custom_action_methods()
    {
        ActionLoggerService::macro('approve', function (array $extraProperties = []) {
            return $this->log(Action::UPDATED, array_merge(['status' => 'approved'], $extraProperties));
        });

        ActionLoggerService::macro('reject', function (array $extraProperties = []) {
            return $this->log(Action::UPDATED, array_merge(['status' => 'rejected'], $extraProperties));
        });

        $user = User::factory()->create();
        $post = Post::factory()->create();

        ActionLogger::on($post)
            ->by($user)
            ->approve(['reason' => 'Approved by manager']);

        ActionLogger::on($post)
            ->by($user)
            ->reject(['reason' => 'Missing documentation']);

        $activities = Activity::all();
        $this->assertCount(2, $activities);

        $this->assertEquals(
            ['status' => 'approved', 'reason' => 'Approved by manager'],
            $activities[0]->properties->toArray()
        );

        $this->assertEquals(
            ['status' => 'rejected', 'reason' => 'Missing documentation'],
            $activities[1]->properties->toArray()
        );
    }
} 