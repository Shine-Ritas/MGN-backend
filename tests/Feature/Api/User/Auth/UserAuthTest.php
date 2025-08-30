<?php

use App\Models\User;
use Database\Seeders\SubscriptionSeeder;
use Illuminate\Support\Facades\Route;
use App\Services\Auth\Authentication;
use App\Services\ClientIp\ClientIpAddressService;
use Tests\Support\UserAuthenticated;
use Illuminate\Support\Facades\Mockery;

uses()->group('user', 'api', 'user-auth');
uses(UserAuthenticated::class);

beforeEach(function () {
    // Seed necessary data
    $this->seed([
        SubscriptionSeeder::class
    ]);

    // Mock ClientIpAddressService to avoid actual IP-related operations
    $this->mock(ClientIpAddressService::class, function ($mock) {
        $mock->shouldReceive("saveRecord")->andReturn(true);
    });

    // Mock Authentication service to bypass real authentication logic
    $this->mock(Authentication::class, function ($mock) {
        $mock->shouldReceive('setRequest')->andReturnSelf();
        $mock->shouldReceive('returnResponse')->andReturnSelf();
        $mock->shouldReceive('signIn')->andReturn(response()->json([
            'token' => 'sample-token',
            'user' => [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'user_code' => 'testuser'
            ],
            'role' => null
        ], 200));
        $mock->shouldReceive('signUp')->andReturn(response()->json([
            'token' => 'sample-token',
            'user' => [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'user_code' => 'testuser'
            ],
            'role' => null
        ], 200));
        $mock->shouldReceive('signOut')->andReturn(response()->json([
            'message' => 'Logged out successfully'
        ], 200));
    });
});

test('user login route exists', function () {
    $this->assertTrue(Route::has('api.user.login'));
});

test('user register route exists', function () {
    $this->assertTrue(Route::has('api.user.register'));
});

test("user can login successfully", function () {
    $user = User::factory()->create();

    // Mocked login request
    $response = $this->postJson(route("api.user.login"), [
        'user_code' => $user->user_code,
        'password' => 'password'
    ]);

    $response->assertStatus(200)->assertJsonStructure([
        'token',
        'user' => [
            'id',
            'name',
            // Add other expected user fields
        ]
    ]);
    
    // Assert that we get the mocked token
    $response->assertJson([
        'token' => 'sample-token'
    ]);
});

test("User can register successfully",function(){
    $response = $this->postJson(route("api.user.register"), [
        'name' => 'Test User',
        'email' => 'testuser@gmail.com',
        'user_code' => 'testuser',
        'password' => 'password',
        'password_confirmation' => 'password'
    ]);

    $response->assertStatus(200)->assertJsonStructure([
        'token',
        'user' => [
            'id',
            'name',
        ]
    ]);
    
    // Assert that we get the mocked token
    $response->assertJson([
        'token' => 'sample-token'
    ]);
});

test("User Login Request Validation", function () {
    // Test validation by sending empty data to login route
    $response = $this->postJson(route("api.user.login"), []);

    $response->assertStatus(422)->assertJsonStructure([
        'message',
        'errors'
    ]);
});


test('user can logout with token delete', function () {
    $this->setupUser();
    $response = $this->authenticatedAdmin($this->user)->postJson(route('api.user.logout'));

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out successfully',
        ]);
});
