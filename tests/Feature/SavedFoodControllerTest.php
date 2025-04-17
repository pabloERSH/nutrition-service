<?php

use App\Models\SavedFood;
use App\Models\User;
use App\Models\EatenFood;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    auth()->logout();
    $this->user = User::factory()->create();
});

afterAll(function () {
    User::truncate();
    EatenFood::truncate();
    SavedFood::truncate();
});

it('denies access to list saved foods without authentication', function () {
    $response = $this->getJson('/api/v1/saved-foods');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('lists saved foods for authenticated user', function () {
    Sanctum::actingAs($this->user);

    SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Food 1',
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);
    SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Food 2',
        'proteins' => 15.00,
        'fats' => 10.00,
        'carbs' => 30.00,
    ]);
    $otherUser = User::factory()->create();
    SavedFood::create([
        'user_id' => $otherUser->id,
        'food_name' => 'Other Food',
        'proteins' => 5.00,
        'fats' => 2.00,
        'carbs' => 10.00,
    ]);

    $response = $this->getJson('/api/v1/saved-foods');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'food_name', 'proteins', 'fats', 'carbs', 'kcal']],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJsonFragment(['food_name' => 'Food 1'])
        ->assertJsonFragment(['food_name' => 'Food 2']);
});

it('denies access to store saved food without authentication', function () {
    $data = [
        'food_name' => 'Test Food',
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ];

    $response = $this->postJson('/api/v1/saved-foods', $data);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('stores a new saved food', function () {
    Sanctum::actingAs($this->user);

    $data = [
        'food_name' => 'Test Food',
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ];

    $response = $this->postJson('/api/v1/saved-foods', $data);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Food saved successfully'])
        ->assertJsonStructure(['data' => ['id', 'food_name', 'proteins', 'fats', 'carbs']]);

    $this->assertDatabaseHas('saved_foods', [
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'proteins' => '10.00',
        'fats' => '5.00',
        'carbs' => '20.00',
    ]);
});

it('fails to store saved food with invalid data', function () {
    Sanctum::actingAs($this->user);

    $data = [
        'food_name' => '',
        'proteins' => -1,
        'fats' => 100.00,
        'carbs' => 'invalid',
    ];

    $response = $this->postJson('/api/v1/saved-foods', $data);

    $response->assertStatus(422)
        ->assertJson(['error' => 'Validation failed'])
        ->assertJsonFragment(['The food name is required.'])
        ->assertJsonFragment(['Proteins cannot be negative.'])
        ->assertJsonFragment(['Fats cannot exceed 99.99.'])
        ->assertJsonFragment(['Carbohydrates must be a number.']);
});

it('fails to store duplicate saved food due to unique constraint', function () {
    Sanctum::actingAs($this->user);

    SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'proteins' => 12.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $data = [
        'food_name' => 'Test Food',
        'proteins' => 12.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ];

    $response = $this->postJson('/api/v1/saved-foods', $data);

    $response->assertStatus(422)
        ->assertJson(['error' => 'Validation failed',
            "message"=> "The provided data is invalid.",
            "errors" => [
                "A food with these nutritional values already exists."
            ]
    ]);
});

it('denies access to update saved food without authentication', function () {
    $savedFood = SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'proteins' => 11.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $data = [
        'food_name' => 'Updated Food',
        'proteins' => 15.00,
        'fats' => 10.00,
        'carbs' => 30.00,
    ];

    $response = $this->patchJson("/api/v1/saved-foods/{$savedFood->id}", $data);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('updates a saved food', function () {
    Sanctum::actingAs($this->user);

    $savedFood = SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Old Food',
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $data = [
        'food_name' => 'Updated Food',
        'proteins' => 15.00,
        'fats' => 10.00,
        'carbs' => 30.00,
    ];

    $response = $this->patchJson("/api/v1/saved-foods/{$savedFood->id}", $data);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Food updated successfully']);

    $this->assertDatabaseHas('saved_foods', [
        'id' => $savedFood->id,
        'food_name' => 'Updated Food',
        'proteins' => '15.00',
        'fats' => '10.00',
        'carbs' => '30.00',
    ]);
});

it('fails to update another user\'s saved food', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create();
    $savedFood = SavedFood::create([
        'user_id' => $otherUser->id,
        'food_name' => 'Other Food',
        'proteins' => 5.50,
        'fats' => 2.00,
        'carbs' => 10.00,
    ]);

    $data = [
        'food_name' => 'Updated Food',
        'proteins' => 15.50,
        'fats' => 10.00,
        'carbs' => 30.00,
    ];

    $response = $this->patchJson("/api/v1/saved-foods/{$savedFood->id}", $data);

    $response->assertStatus(403)
        ->assertJson(['error' => 'Forbidden']);
});

it('denies access to delete saved food without authentication', function () {
    $savedFood = SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'proteins' => 10.33,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);

    $response = $this->deleteJson("/api/v1/saved-foods/{$savedFood->id}");

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('deletes a saved food and triggers eaten foods update', function () {
    Sanctum::actingAs($this->user);

    $savedFood = SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Test Food',
        'proteins' => 9.50,
        'fats' => 5.00,
        'carbs' => 20.00,
    ]);
    $eatenFood = EatenFood::create([
        'user_id' => $this->user->id,
        'food_id' => $savedFood->id,
        'weight' => 100.00,
        'food_name' => null,
        'proteins' => null,
        'fats' => null,
        'carbs' => null,
        'created_at' => now(),
    ]);

    $response = $this->deleteJson("/api/v1/saved-foods/{$savedFood->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Food deleted successfully']);

    $this->assertDatabaseMissing('saved_foods', ['id' => $savedFood->id]);
    $this->assertDatabaseHas('eaten_foods', [
        'id' => $eatenFood->id,
        'food_id' => null,
        'food_name' => 'Test Food',
        'proteins' => '9.50',
        'fats' => '5.00',
        'carbs' => '20.00',
    ]);
});

it('fails to delete another user\'s saved food', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create();
    $savedFood = SavedFood::create([
        'user_id' => $otherUser->id,
        'food_name' => 'Other Food',
        'proteins' => 5.00,
        'fats' => 2.33,
        'carbs' => 10.00,
    ]);

    $response = $this->deleteJson("/api/v1/saved-foods/{$savedFood->id}");

    $response->assertStatus(403)
        ->assertJson(['error' => 'Forbidden']);
});

it('denies access to search saved foods without authentication', function () {
    $response = $this->getJson('/api/v1/saved-foods/search?food_name=Chicken');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('searches saved foods by name', function () {
    Sanctum::actingAs($this->user);

    SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Chicken Breast',
        'proteins' => 20.00,
        'fats' => 5.00,
        'carbs' => 0.00,
    ]);
    SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Beef Steak',
        'proteins' => 25.00,
        'fats' => 15.00,
        'carbs' => 0.00,
    ]);

    $response = $this->getJson('/api/v1/saved-foods/search?food_name=Chicken');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['food_name' => 'Chicken Breast']);
});

it('respects unique constraint on update', function () {
    Sanctum::actingAs($this->user);

    $savedFood1 = SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Food 1',
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 23.33,
    ]);
    $savedFood2 = SavedFood::create([
        'user_id' => $this->user->id,
        'food_name' => 'Food 2',
        'proteins' => 15.00,
        'fats' => 10.00,
        'carbs' => 31.00,
    ]);

    $data = [
        'food_name' => 'Food 1',
        'proteins' => 10.00,
        'fats' => 5.00,
        'carbs' => 23.33,
    ];

    $response = $this->patchJson("/api/v1/saved-foods/{$savedFood2->id}", $data);

    $response->assertStatus(422)
        ->assertJson(['error' => 'Validation failed',
            "message"=> "The provided data is invalid.",
            "errors" => [
                "A food with these nutritional values already exists."
            ]
        ]);
});
