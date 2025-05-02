<?php

use App\Helpers\KcalCountHelper;
use App\Models\EatenFood;
use App\Models\SavedFood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(\tests\TestCase::class, RefreshDatabase::class);

it('calculates kcal correctly for saved food', function () {
    $user = User::factory()->create();
    $food = SavedFood::factory()->create([
        'user_id' => $user->id,
        'proteins' => 10,
        'fats' => 20,
        'carbs' => 30,
    ]);

    $foods = SavedFood::where('id', $food->id)->paginate();
    $foods = KcalCountHelper::addKcal($foods);

    $expectedKcal = round((10 * 4) + (20 * 9) + (30 * 4), 2); // 40 + 180 + 120 = 340
    expect($foods->first()->kcal)->toBe($expectedKcal);
});

it('calculates kcal with weight correctly for eaten food', function () {
    $user = User::factory()->create();
    $food = EatenFood::factory()->create([
        'user_id' => $user->id,
        'proteins' => 10,
        'fats' => 20,
        'carbs' => 30,
        'weight' => 150,
    ]);

    $foods = EatenFood::where('id', $food->id)->paginate();
    $foods = KcalCountHelper::addKcalWeight($foods);

    $baseKcal = (10 * 4) + (20 * 9) + (30 * 4); // 340
    $expectedKcal = round((150 / 100) * $baseKcal, 2); // 1.5 * 340 = 510
    expect($foods->first()->kcal)->toBe($expectedKcal);
});
