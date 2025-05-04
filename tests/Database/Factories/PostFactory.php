<?php

namespace Tests\Database\Factories;

use Tests\Models\Post;
use Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'published_at' => $this->faker->optional()->dateTimeThisMonth,
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
                'published_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
                'published_at' => null,
            ];
        });
    }

    /**
     * Indicate that the post is archived.
     */
    public function archived(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'archived',
                'published_at' => now()->subMonths(3),
            ];
        });
    }
} 