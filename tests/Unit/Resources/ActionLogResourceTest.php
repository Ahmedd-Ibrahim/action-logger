<?php

namespace Tests\Unit\Resources;

use Illuminate\Support\Facades\Lang;
use Tests\TestCase;
use Tests\Models\User;
use Tests\Models\Post;
use BIM\ActionLogger\Resources\ActionLogResource;
use BIM\ActionLogger\Enums\Action;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionLogResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_transform_an_activity_to_array()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $activity = Activity::create([
            'log_name' => 'default',
            'description' => Action::CREATED->value(),
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'properties' => ['key' => 'value'],
            'batch_uuid' => 'test-uuid',
        ]);

        $resource = new ActionLogResource($activity);
        $array = $resource->toArray(request());

        $description = Lang::get('action-logger::messages.'.$activity->description, [
            'model' => Lang::get('action-logger::models.'.strtolower(class_basename($activity->subject))),
            'user' => $activity->causer->name,
            ...$activity->properties,
        ]);

        $this->assertEquals($activity->id, $array['id']);
        $this->assertEquals('default', $array['log_name']);
        $this->assertEquals($description, $array['description']);
        $this->assertEquals(Post::class, $array['subject_type']);
        $this->assertEquals($post->id, $array['subject_id']);
        $this->assertEquals(User::class, $array['causer_type']);
        $this->assertEquals($user->id, $array['causer_id']);
        $this->assertEquals(['key' => 'value'], $array['properties']->toArray());
        $this->assertEquals('test-uuid', $array['batch_uuid']);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    /** @test */
    public function it_can_create_a_resource_collection()
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create();

        $activities = collect();
        foreach ($posts as $post) {
            $activities->push(Activity::create([
                'log_name' => 'default',
                'description' => Action::CREATED->value(),
                'subject_type' => Post::class,
                'subject_id' => $post->id,
                'causer_type' => User::class,
                'causer_id' => $user->id,
                'properties' => [],
                'batch_uuid' => 'test-uuid',
            ]));
        }

        $collection = ActionLogResource::collection($activities);
        $this->assertCount(3, $collection->toArray(request()));
    }

    /** @test */
    public function it_can_create_a_resource_collection_with_query_modifications()
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create();

        $activities = collect();
        foreach ($posts as $post) {
            $activities->push(Activity::create([
                'log_name' => 'default',
                'description' => Action::CREATED->value(),
                'subject_type' => Post::class,
                'subject_id' => $post->id,
                'causer_type' => User::class,
                'causer_id' => $user->id,
                'properties' => [],
                'batch_uuid' => 'test-uuid',
            ]));
        }

        $collection = ActionLogResource::collectionWithQuery(
            $activities,
            fn($query) => $query->where('log_name', 'default')
        );
        dd($collection->toArray(request()));

        $this->assertCount(3, $collection->toArray(request()));
    }
} 