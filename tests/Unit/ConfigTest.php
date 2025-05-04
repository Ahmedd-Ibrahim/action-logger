<?php

namespace Tests\Unit;

use Tests\TestCase;
use BIM\ActionLogger\Processors\BatchActionProcessor;

class ConfigTest extends TestCase
{
    /** @test */
    public function it_has_default_configuration()
    {
        $this->assertEquals(BatchActionProcessor::class, config('action-logger.default_processors.default'));
        $this->assertIsArray(config('action-logger.route_processors'));
        $this->assertIsArray(config('action-logger.controller_processors'));
        $this->assertEquals(['delete_discarded' => false], config('action-logger.batch'));
    }

    /** @test */
    public function it_can_override_configuration()
    {
        config(['action-logger.default_processors.default' => 'CustomProcessor']);
        $this->assertEquals('CustomProcessor', config('action-logger.default_processors.default'));

        config(['action-logger.route_processors' => ['test.route' => 'TestProcessor']]);
        $this->assertEquals(['test.route' => 'TestProcessor'], config('action-logger.route_processors'));

        config(['action-logger.controller_processors' => ['App\Controller@action' => 'TestProcessor']]);
        $this->assertEquals(['App\Controller@action' => 'TestProcessor'], config('action-logger.controller_processors'));

        config(['action-logger.batch.delete_discarded' => true]);
        $this->assertEquals(['delete_discarded' => true], config('action-logger.batch'));
    }
} 