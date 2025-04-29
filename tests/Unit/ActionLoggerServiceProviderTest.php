<?php

namespace Tests\Unit;

use Spatie\Activitylog\Facades\LogBatch;
use Tests\TestCase;
use BIM\ActionLogger\ActionLoggerServiceProvider;
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Facades\ActionLogger;

class ActionLoggerServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_the_action_logger_service()
    {
        $this->assertTrue($this->app->bound('action-logger'));
        $this->assertInstanceOf(ActionLoggerService::class, $this->app->make('action-logger'));
    }


    /** @test */
    public function it_registers_the_action_logger_facade()
    {
        $this->assertInstanceOf(ActionLoggerService::class, ActionLogger::getFacadeRoot());
    }


    /** @test */
    public function it_publishes_configuration()
    {
        $this->assertArrayHasKey('action-logger', $this->app['config']->all());
    }

    /** @test */
    public function it_publishes_translations()
    {
        $this->assertTrue($this->app['translator']->has('action-logger::messages'));
        $this->assertTrue($this->app['translator']->has('action-logger::models'));
    }
} 