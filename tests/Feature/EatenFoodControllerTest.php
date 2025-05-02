<?php

use App\Models\EatenFood;
use App\Models\SavedFood;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    auth()->logout();
    $this->user = User::factory()->create();
    EatenFood::truncate();
});

// Index Tests
it('denies access to list eaten foods without authentication', function () {
    $response = $this->getJson('/api/v1/eaten-foods');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('lists eaten foods for authenticated user with pagination', function () {
    Sanctum::actingAs($this->user);

    EatenFood::factory()->count(15)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/eaten-foods?per_page=5');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'food_name', 'proteins', 'fats', 'carbs', 'weight', 'eaten_at', 'kcal']],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJson(['meta' => ['per_page' => 5, 'total' => 15]]);
});

// Store Tests
it('denies access to store eaten food without authentication', function () {
    $foodData = EatenFood::factory()->make()->toArray();

    $response = $this->postJson('/api/v1/eaten-foods', $foodData);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('stores eaten food with saved food successfully', function () {
    Sanctum::actingAs($this->user);

    $savedFood = SavedFood::factory()->create(['user_id' => $this->user->id]);
    $foodData = [
        'food_id' => $savedFood->id,
        'eaten_at' => Carbon::today()->format('Y-m-d'),
        'weight' => 100,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $foodData);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Food saved successfully',
            'data' => [
                'food_id' => $savedFood->id,
                'eaten_at' => $foodData['eaten_at'],
                'weight' => '100.00',
            ],
        ]);

    $this->assertDatabaseHas('eaten_foods', [
        'user_id' => $this->user->id,
        'food_id' => $savedFood->id,
        'eaten_at' => $foodData['eaten_at'],
        'weight' => '100.00',
    ]);
});

it('stores eaten food with custom nutrients successfully', function () {
    Sanctum::actingAs($this->user);

    $foodData = [
        'food_name' => 'Custom Meal',
        'proteins' => 10,
        'fats' => 20,
        'carbs' => 30,
        'eaten_at' => Carbon::today()->format('Y-m-d'),
        'weight' => 150,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $foodData);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Food saved successfully',
            'data' => [
                'food_name' => 'Custom Meal',
                'proteins' => '10.00',
                'fats' => '20.00',
                'carbs' => '30.00',
                'eaten_at' => $foodData['eaten_at'],
                'weight' => '150.00',
            ],
        ]);

    $this->assertDatabaseHas('eaten_foods', [
        'user_id' => $this->user->id,
        'food_name' => 'Custom Meal',
        'proteins' => '10.00',
        'fats' => '20.00',
        'carbs' => '30.00',
        'eaten_at' => $foodData['eaten_at'],
        'weight' => '150.00',
    ]);
});

it('fails to store eaten food with invalid date', function () {
    Sanctum::actingAs($this->user);

    $foodData = [
        'food_name' => 'Apple',
        'proteins' => 0.5,
        'fats' => 0.2,
        'carbs' => 14,
        'eaten_at' => Carbon::today()->subDays(31)->format('Y-m-d'),
        'weight' => 100,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $foodData);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'Validation failed',
            'errors' => ['The eaten_at should not be earlier than 30 days.'],
        ]);
});

it('fails to store eaten food with excessive nutrients', function () {
    Sanctum::actingAs($this->user);

    $foodData = [
        'food_name' => 'Overloaded Meal',
        'proteins' => 50,
        'fats' => 50,
        'carbs' => 50,
        'eaten_at' => Carbon::today()->format('Y-m-d'),
        'weight' => 100,
    ];

    $response = $this->postJson('/api/v1/eaten-foods', $foodData);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'Validation failed',
            'errors' => ['The total amount of nutrients should be less than or equal to 100 grams.'],
        ]);
});

// Delete Tests
it('deletes an eaten food successfully', function () {
    Sanctum::actingAs($this->user);

    $eatenFood = EatenFood::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/eaten-foods/{$eatenFood->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Food deleted successfully']);

    $this->assertDatabaseMissing('eaten_foods', ['id' => $eatenFood->id]);
});

it('fails to delete another user\'s eaten food', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create();
    $eatenFood = EatenFood::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->deleteJson("/api/v1/eaten-foods/{$eatenFood->id}");

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Forbidden',
            'message' => 'You do not have privileges to delete this resource.',
        ]);
});

// Show By Date Tests
it('returns empty data for show by date with no foods', function () {
    Sanctum::actingAs($this->user);
    $date = Carbon::today()->format('Y-m-d');
    $otherDate = Carbon::today()->subDays(10)->format('Y-m-d');

    EatenFood::factory()->create(['user_id' => $this->user->id, 'eaten_at' => $otherDate]);

    $response = $this->getJson("/api/v1/eaten-foods/show-by-date?date={$date}");
    $response->assertStatus(200)
        ->assertJsonCount(0, 'data.items')
        ->assertJsonStructure([
            'data' => [
                'items' => [],
                'Total proteins',
                'Total fats',
                'Total carbs',
                'Total kcal',
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJson([
            'data' => [
                'Total proteins' => 0,
                'Total fats' => 0,
                'Total carbs' => 0,
                'Total kcal' => 0,
            ],
        ]);
});

it('shows eaten foods by date with aggregation', function () {
    Sanctum::actingAs($this->user);

    $date = Carbon::today()->format('Y-m-d');
    $otherDate = Carbon::today()->subDays(10)->format('Y-m-d');

    EatenFood::factory()->create(['user_id' => $this->user->id, 'eaten_at' => $otherDate]);
    EatenFood::factory()->create(['user_id' => $this->user->id, 'eaten_at' => $otherDate]);

    EatenFood::factory()->create([
        'user_id' => $this->user->id,
        'eaten_at' => $date,
        'proteins' => 10,
        'fats' => 20,
        'carbs' => 30,
        'weight' => 100,
    ]);
    EatenFood::factory()->create([
        'user_id' => $this->user->id,
        'eaten_at' => $date,
        'proteins' => 5,
        'fats' => 10,
        'carbs' => 15,
        'weight' => 200,
    ]);

    $response = $this->getJson("/api/v1/eaten-foods/show-by-date?date={$date}&per_page=5");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data.items')
        ->assertJsonStructure([
            'data' => [
                'items' => [
                    '*' => ['id', 'food_name', 'proteins', 'fats', 'carbs', 'weight', 'eaten_at', 'kcal'],
                ],
                'Total proteins',
                'Total fats',
                'Total carbs',
                'Total kcal',
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJson([
            'data' => [
                'Total proteins' => 15,
                'Total fats' => 30,
                'Total carbs' => 45,
            ],
        ]);
});

// Authorization Tests
it('denies access to show eaten foods by date without authentication', function () {
    $date = Carbon::today()->format('Y-m-d');
    $response = $this->getJson("/api/v1/eaten-foods/show-by-date?date={$date}");

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});
