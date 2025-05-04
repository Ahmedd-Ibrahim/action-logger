<?php

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\Subscription;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'duration' => $this->faker->numberBetween(30, 365),
            'features' => [
                'feature1' => $this->faker->word(),
                'feature2' => $this->faker->word(),
                'feature3' => $this->faker->word(),
            ],
            'status' => $this->faker->randomElement(['active', 'inactive', 'pending']),
            'user_id' => null, // This should be set when creating the subscription
            'batch_uuid' => null,
        ];
    }

    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }

    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }
} 