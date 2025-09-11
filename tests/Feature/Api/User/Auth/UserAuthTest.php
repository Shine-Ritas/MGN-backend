<?php

use App\Models\User;
use App\Services\Auth\Authentication;
use App\Services\ClientIp\ClientIpAddressService;
use Database\Seeders\SubscriptionSeeder;
use Illuminate\Support\Facades\Route;
use Tests\Support\UserAuthenticated;

uses()->group('user', 'api', 'user-auth');
uses(UserAuthenticated::class);

beforeEach(function () {
    seedTestData();
    mockExternalServices();
    mockAuthenticationService();
});

function seedTestData(): void
{
    test()->seed([SubscriptionSeeder::class]);
}

function mockExternalServices(): void
{
    test()->mock(ClientIpAddressService::class, function ($mock) {
        $mock->shouldReceive('saveRecord')->andReturn(true);
    });
}

function mockAuthenticationService(): void
{
    test()->mock(Authentication::class, function ($mock) {
        $mock->shouldReceive('setRequest')->andReturnSelf();
        $mock->shouldReceive('returnResponse')->andReturnSelf();

        $mockResponse = getMockAuthResponse();

        $mock->shouldReceive('signIn')->andReturn($mockResponse);
        $mock->shouldReceive('signUp')->andReturn($mockResponse);
        $mock->shouldReceive('signOut')->andReturn(response()->json([
            'message' => 'Logged out successfully',
        ]));
    });
}

function getMockAuthResponse()
{
    return response()->json([
        'token' => 'sample-token',
        'user' => [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user_code' => 'testuser',
        ],
        'role' => null,
    ]);
}

// Route existence tests
test('user login route exists', function () {
    expect(Route::has('api.user.login'))->toBeTrue();
});

test('user register route exists', function () {
    expect(Route::has('api.user.register'))->toBeTrue();
});

// Authentication functionality tests
test('user can login successfully', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('api.user.login'), [
        'user_code' => $user->user_code,
        'password' => 'password',
    ]);

    assertSuccessfulAuthResponse($response);
});

test('user can register successfully', function () {
    $registrationData = [
        'name' => 'Test User',
        'email' => 'testuser@gmail.com',
        'user_code' => 'testuser',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $response = $this->postJson(route('api.user.register'), $registrationData);

    assertSuccessfulAuthResponse($response);
});

test('user login validation works', function () {
    $response = $this->postJson(route('api.user.login'), []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors',
        ]);
});

test('user can logout successfully', function () {
    $this->setupUser();

    $response = $this->authenticatedAdmin($this->user)
        ->postJson(route('api.user.logout'));

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out successfully',
        ]);
});

// Helper function for authentication response assertions
function assertSuccessfulAuthResponse($response): void
{
    $response->assertStatus(200)
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name'],
            'role',
        ])
        ->assertJson([
            'token' => 'sample-token',
        ]);
}
