<?php

use App\Models\EatenFood;
use App\Models\SavedFood;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    auth() -> logout();
    $this->user = User::factory()->create();
});

afterAll(function () {
    User::truncate();
    EatenFood::truncate();
    SavedFood::truncate();
});

it('denies access to list eaten foods without authentication', function () {
    $response = $this->getJson('/api/v1/eaten-foods');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('lists eaten foods for authenticated user', function () {
    Sanctum::actingAs($this->user);

    EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Food 1',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);
    EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Food 2',
        'weight' => 200.00,
        'proteins' => 15.00,
        'fats' => 10.00,
        'carbs' => 30.00,
    ]);
    $otherUser = User::factory()->create();
    EatenFood::create([
        'user_id' => $otherUser->id,
        'food_name' => 'Other Food',
        'weight' => 50.00,
        'proteins' => 5.00,
        'fats' => 2.00,
        'carbs' => 10.00,
    ]);

    $response = $this->getJson('/api/v1/eaten-foods');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'food_name', 'weight', 'proteins', 'fats', 'carbs', 'kcal']],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJsonFragment(['food_name' => 'Food 1'])
        ->assertJsonFragment(['food_name' => 'Food 2']);
});

it('denies access to store eaten food without authentication', function () {
    $data = [
        'food_name' => 'Test Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $data);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('stores a new eaten food with food_id', function () {
    Sanctum::actingAs($this->user);

    $savedFood = SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Saved Food',
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $data = [
        'food_id' => $savedFood->id,
        'weight' => 100.00,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $data);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Food saved successfully'])
        ->assertJsonStructure(['data' => ['id', 'food_name', 'weight', 'proteins', 'fats', 'carbs']]);

    $this->assertDatabaseHas('eaten_foods', [
        'user_id' => $this->user->id,
        'food_id' => $savedFood->id,
        'weight' => '100.00',
        'food_name' => null,
        'proteins' => null,
        'fats' => null,
        'carbs' => null,
    ]);
});

it('stores a new eaten food without food_id', function () {
    Sanctum::actingAs($this->user);

    $data = [
        'food_name' => 'Test Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $data);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Food saved successfully'])
        ->assertJsonStructure(['data' => ['id', 'food_name', 'weight', 'proteins', 'fats', 'carbs']]);

    $this->assertDatabaseHas('eaten_foods', [
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'weight' => '100.00',
        'proteins' => '10.00',
        'fats' => '5.00',
        'carbs' => '20.00',
        'food_id' => null,
    ]);
});

it('fails to store eaten food with invalid data violating CHECK constraint', function () {
    Sanctum::actingAs($this->user);

    $data = [
        'food_id' => 1,
        'food_name' => 'Test Food',
        'weight' => 100.00,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $data);

    $response->assertStatus(422)
        ->assertJson(['error' => 'Validation failed'])
        ->assertJsonFragment(['Cannot provide both food_id and nutrients.']);
});

it('fails to store eaten food with negative weight', function () {
    Sanctum::actingAs($this->user);

    $data = [
        'food_name' => 'Test Food',
        'weight' => -100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $data);

    $response->assertStatus(422)
        ->assertJson(['error' => 'Validation failed'])
        ->assertJsonFragment(['Weight cannot be negative.']);
});

it('denies access to update eaten food without authentication', function () {
    $eatenFood = EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $data = [
        'food_name' => 'Updated Food',
        'weight' => 200.00,
        'proteins' => 15.00,
        'fats' => 10.00,
        'carbs' => 30.00,
    ];

    $response = $this->patchJson("/api/v1/eaten-foods/{$eatenFood->id}", $data);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('updates an eaten food', function () {
    Sanctum::actingAs($this->user);

    $eatenFood = EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Old Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $data = [
        'food_name' => 'Updated Food',
        'weight' => 200.00,
        'proteins' => 15.00,
        'fats' => 10.00,
        'carbs' => 30.00,
    ];

    $response = $this->patchJson("/api/v1/eaten-foods/{$eatenFood->id}", $data);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Food updated successfully']);

    $this->assertDatabaseHas('eaten_foods', [
        'id' => $eatenFood->id,
        'food_name' => 'Updated Food',
        'weight' => '200.00',
        'proteins' => '15.00',
        'fats' => '10.00',
        'carbs' => '30.00',
    ]);
});

it('fails to update another user\'s eaten food', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create();
    $eatenFood = EatenFood::create([
        'user_id' => $otherUser->id,
        'food_name' => 'Other Food',
        'weight' => 50.00,
        'proteins' => 5.00,
        'fats' => 2.00,
        'carbs' => 10.00,
    ]);

    $data = [
        'food_name' => 'Updated Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ];

    $response = $this->patchJson("/api/v1/eaten-foods/{$eatenFood->id}", $data);

    $response->assertStatus(403)
        ->assertJson(['error' => 'Forbidden']);
});

it('denies access to delete eaten food without authentication', function () {
    $eatenFood = EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $response = $this->deleteJson("/api/v1/eaten-foods/{$eatenFood->id}");

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('deletes an eaten food', function () {
    Sanctum::actingAs($this->user);

    $eatenFood = EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $response = $this->deleteJson("/api/v1/eaten-foods/{$eatenFood->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Food deleted successfully']);

    $this->assertDatabaseMissing('eaten_foods', ['id' => $eatenFood->id]);
});

it('fails to delete another user\'s eaten food', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create();
    $eatenFood = EatenFood::create([
        'user_id' => $otherUser->id,
        'food_name' => 'Other Food',
        'weight' => 50.00,
        'proteins' => 5.00,
        'fats' => 2.00,
        'carbs' => 10.00,
    ]);

    $response = $this->deleteJson("/api/v1/eaten-foods/{$eatenFood->id}");

    $response->assertStatus(403)
        ->assertJson(['error' => 'Forbidden']);
});

it('denies access to show eaten foods by date without authentication', function () {
    $response = $this->getJson('/api/v1/eaten-foods/show-by-date?date=2025-04-16');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('shows eaten foods by date', function () {
    Sanctum::actingAs($this->user);

    EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
        'created_at' => Carbon::parse('2025-04-16 10:00:00 UTC')->toISOString(),
    ]);
    EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Other Food',
        'weight' => 50.00,
        'proteins' => 5.00,
        'fats' => 2.00,
        'carbs' => 10.00,
        'created_at' => Carbon::parse('2025-04-17 10:00:00 UTC')->toISOString(),
    ]);

    $response = $this->getJson('/api/v1/eaten-foods/show-by-date?date=2025-04-16');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.0')
        ->assertJsonStructure([
            'data' => [
                'Total proteins',
                'Total fats',
                'Total carbs',
                'Total kcal',
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJsonFragment(['food_name' => 'Test Food']);
});

it('fails to show eaten foods with invalid date', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/v1/eaten-foods/show-by-date?date=invalid');

    $response->assertStatus(422)
        ->assertJson(['error' => 'Date not valid']);
});

it('respects CHECK constraint on update', function () {
    Sanctum::actingAs($this->user);

    $eatenFood = EatenFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'weight' => 100.00,
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
        'created_at' => now(),
    ]);

    $data = [
        'food_id' => 1,
        'food_name' => 'Updated Food',
        'weight' => 200.00,
    ];

    $response = $this->patchJson("/api/v1/eaten-foods/{$eatenFood->id}", $data);

    $response->assertStatus(422)
        ->assertJson(['error' => 'Validation failed'])
        ->assertJsonFragment(['Cannot provide both food_id and nutrients.']);
});
