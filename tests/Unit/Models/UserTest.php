<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Tests\Models\User;
use Tests\Models\Post;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_can_be_created_with_factory()
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        // Verify user was created with correct attributes
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }
    
    /** @test */
    public function it_provides_custom_description_for_events()
    {
        $user = User::factory()->create();
        
        // Test the getDescriptionForEvent method
        $this->assertEquals('created user', $user->getDescriptionForEvent('created'));
        $this->assertEquals('updated user', $user->getDescriptionForEvent('updated'));
        $this->assertEquals('deleted user', $user->getDescriptionForEvent('deleted'));
    }
    
    /** @test */
    public function it_has_relationships()
    {
        $user = User::factory()->create();
        
        // Verify relationship methods exist
        $this->assertTrue(method_exists($user, 'profile'));
        $this->assertTrue(method_exists($user, 'subscriptions'));
    }
} 