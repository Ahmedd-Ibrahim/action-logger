<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use BIM\ActionLogger\Middleware\AutoBatchLoggingMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;

class AutoBatchLoggingMiddlewareTest extends TestCase
{
    protected $middleware;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create middleware
        $this->middleware = new AutoBatchLoggingMiddleware();
    }
    
    /** @test */
    public function it_passes_request_to_next_middleware()
    {
        // Create a request with a route
        $request = new Request();
        $route = new Route(['GET'], 'users', ['as' => 'users.index', 'uses' => 'App\Http\Controllers\UserController@index']);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });
        
        // The handle method should call the next closure
        $called = false;
        $next = function ($req) use (&$called, $request) {
            $called = true;
            $this->assertSame($request, $req);
            return new Response('OK', 200);
        };
        
        // Call middleware
        $response = $this->middleware->handle($request, $next);
        
        // Verify the next closure was called
        $this->assertTrue($called);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    /** @test */
    public function it_skips_routes_in_excluded_list()
    {
        // Mock config to exclude route
        $this->app['config']->set('action-logger.excluded_routes', ['test/*']);
        
        // Create request for excluded route
        $request = new Request();
        $request->server->set('REQUEST_URI', '/test/route');
        
        $called = false;
        $next = function ($req) use (&$called) {
            $called = true;
            return new Response('OK', 200);
        };
        
        // Call middleware
        $response = $this->middleware->handle($request, $next);
        
        // Verify next was called and request was passed through
        $this->assertTrue($called);
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    /** @test */
    public function it_handles_auto_end_configuration()
    {
        // Test with auto-end enabled
        $this->app['config']->set('action-logger.batch.auto_end', true);
        
        // Create a request with a route
        $request = new Request();
        $route = new Route(['GET'], 'users', ['as' => 'users.index']);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });
        
        // Call middleware
        $called = false;
        $next = function ($req) use (&$called) {
            $called = true;
            return new Response('OK', 200);
        };
        
        $response = $this->middleware->handle($request, $next);
        
        // Verify response
        $this->assertTrue($called);
        $this->assertEquals(200, $response->getStatusCode());
        
        // Test with auto-end disabled
        $this->app['config']->set('action-logger.batch.auto_end', false);
        
        $called = false;
        $response = $this->middleware->handle($request, $next);
        
        // Verify response still works
        $this->assertTrue($called);
        $this->assertEquals(200, $response->getStatusCode());
    }
} 