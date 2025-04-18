<?php

namespace Database\Factories;

use App\Models\SavedFood;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavedFoodFactory extends Factory
{
    protected $model = SavedFood::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'food_name' => $this->faker->unique()->word, // Исправлено: используем word вместо words
            'proteins' => $this->faker->randomFloat(2, 0, 100),
            'fats' => $this->faker->randomFloat(2, 0, 100),
            'carbs' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
