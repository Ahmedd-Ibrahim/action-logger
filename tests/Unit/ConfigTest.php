<?php

namespace Tests\Unit;

use Tests\TestCase;
use BIM\ActionLogger\Enums\Action;

class ConfigTest extends TestCase
{
    /** @test */
    public function it_has_default_configuration()
    {
        $config = $this->app['config']->get('action-logger');

        $this->assertArrayHasKey('model_translations', $config);
        $this->assertArrayHasKey('action_class', $config);
        $this->assertEquals(Action::class, $config['action_class']);
    }

    /** @test */
    public function it_can_override_configuration()
    {
        $this->app['config']->set('action-logger.model_translations', [
            'App\Models\User' => 'custom_user',
            'App\Models\Post' => 'custom_post',
        ]);

        $config = $this->app['config']->get('action-logger');

        $this->assertEquals('custom_user', $config['model_translations']['App\Models\User']);
        $this->assertEquals('custom_post', $config['model_translations']['App\Models\Post']);
    }

    /** @test */
    public function it_can_use_custom_action_class()
    {
        $this->app['config']->set('action-logger.action_class', 'App\Enums\CustomAction');

        $config = $this->app['config']->get('action-logger');

        $this->assertEquals('App\Enums\CustomAction', $config['action_class']);
    }
} 