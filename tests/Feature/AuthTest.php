<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('can register a new user', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ]);

    expect(User::count())->toBe(1)
        ->and(User::first()->email)->toBe('test@example.com');
});

it('fails to register with invalid data', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'token',
        ]);

    expect($response->json('token'))->not->toBeEmpty();
});

it('fails to login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'error' => 'Wrong email or password!',
        ]);
});

it('can logout authenticated user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out, token removed',
        ]);

    expect($user->tokens()->count())->toBe(0);
});

it('fails to logout unauthenticated user', function () {
    $response = $this->postJson('/api/v1/logout');

    $response->assertStatus(401);
});
