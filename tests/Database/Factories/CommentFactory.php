<?php

namespace Tests\Database\Factories;

use Tests\Models\Comment;
use Tests\Models\Post;
use Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->paragraph,
        ];
    }
} 