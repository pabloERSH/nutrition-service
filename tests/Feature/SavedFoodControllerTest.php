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

// Index Tests
it('denies access to list saved foods without authentication', function () {
    $response = $this->getJson('/api/v1/saved-foods');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('lists saved foods for authenticated user with pagination', function () {
    Sanctum::actingAs($this->user);

    $foods = SavedFood::factory()->count(15)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/saved-foods?per_page=5');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'food_name', 'proteins', 'fats', 'carbs', 'kcal']],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJson(['meta' => ['per_page' => 5, 'total' => 15]]);
});

// Store Tests
it('denies access to store saved food without authentication', function () {
    $foodData = SavedFood::factory()->make()->toArray();

    $response = $this->postJson('/api/v1/saved-foods', $foodData);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('stores a new saved food successfully', function () {
    Sanctum::actingAs($this->user);

    $foodData = SavedFood::factory()->make()->toArray();

    $response = $this->postJson('/api/v1/saved-foods', $foodData);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Food saved successfully',
            'data' => [
                'food_name' => $foodData['food_name'],
                'proteins' => $foodData['proteins'],
                'fats' => $foodData['fats'],
                'carbs' => $foodData['carbs'],
            ]
        ]);

    $this->assertDatabaseHas('saved_foods', array_merge($foodData, ['user_id' => $this->user->id]));
});

it('fails to store duplicate saved food', function () {
    Sanctum::actingAs($this->user);

    $existingFood = SavedFood::factory()->create(['user_id' => $this->user->id]);
    $duplicateData = $existingFood->only(['food_name', 'proteins', 'fats', 'carbs']);

    $response = $this->postJson('/api/v1/saved-foods', $duplicateData);

    $response->assertStatus(422)
        ->assertJson([
            "error" => "Validation failed",
            "message" => "The provided data is invalid.",
            "errors" => [
                "A food with these nutritional values already exists."
            ]
        ]);
});

// Update Tests
it('updates a saved food successfully', function () {
    Sanctum::actingAs($this->user);

    // Создаем запись для текущего пользователя
    $savedFood = SavedFood::factory()->create(['user_id' => $this->user->id]);

    // Генерируем новые данные для обновления
    $updateData = [
        'food_name' => 'Updated Food Name',
        'proteins' => 25.50,
        'fats' => 10.75,
        'carbs' => 30.25
    ];

    $response = $this->patchJson("/api/v1/saved-foods/{$savedFood->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Food updated successfully']);

    // Проверяем обновленные данные, включая user_id
    $this->assertDatabaseHas('saved_foods', [
        'id' => $savedFood->id,
        'user_id' => $this->user->id, // Добавляем проверку user_id
        'food_name' => $updateData['food_name'],
        'proteins' => $updateData['proteins'],
        'fats' => $updateData['fats'],
        'carbs' => $updateData['carbs'],
    ]);
});

it('fails to update with duplicate data', function () {
    Sanctum::actingAs($this->user);

    $food1 = SavedFood::factory()->create(['user_id' => $this->user->id]);
    $food2 = SavedFood::factory()->create(['user_id' => $this->user->id]);

    $response = $this->patchJson("/api/v1/saved-foods/{$food2->id}", $food1->only(['food_name', 'proteins', 'fats', 'carbs']));

    $response->assertStatus(422)
        ->assertJson([
            "error" => "Validation failed",
            "message" => "The provided data is invalid.",
            "errors" => [
                "A food with these nutritional values already exists."
            ]
        ]);
});

// Delete Tests
it('deletes a saved food successfully', function () {
    Sanctum::actingAs($this->user);

    $savedFood = SavedFood::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/saved-foods/{$savedFood->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Food deleted successfully']);

    $this->assertDatabaseMissing('saved_foods', ['id' => $savedFood->id]);
});

// Search Tests
it('searches saved foods by name with pagination', function () {
    Sanctum::actingAs($this->user);

    SavedFood::factory()->create([
        'user_id' => $this->user->id,
        'food_name' => 'Chicken Breast'
    ]);
    SavedFood::factory()->create([
        'user_id' => $this->user->id,
        'food_name' => 'Beef Steak'
    ]);

    $response = $this->getJson('/api/v1/saved-foods/search?food_name=Chicken&per_page=1');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['food_name' => 'Chicken Breast'])
        ->assertJson(['meta' => ['per_page' => 1, 'total' => 1]]);
});

// Authorization Tests
it('fails to update another users food', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create();
    $savedFood = SavedFood::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->patchJson("/api/v1/saved-foods/{$savedFood->id}", [
        'food_name' => 'Updated Name',
        'proteins' => 12,
        'fats' => 20,
        'carbs' => 30,
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Forbidden',
            'message' => 'You do not have privileges to update this resource.'
        ]);
});

it('fails to delete another users food', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create();
    $savedFood = SavedFood::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->deleteJson("/api/v1/saved-foods/{$savedFood->id}");

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Forbidden',
            'message' => 'You do not have privileges to delete this resource.'
        ]);
});
