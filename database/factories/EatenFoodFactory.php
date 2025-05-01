<?php

namespace Database\Factories;

use App\Models\EatenFood;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EatenFoodFactory extends Factory
{
    protected $model = EatenFood::class;

    public function definition()
    {
        $minDate = Carbon::today()->subDays(30)->format('Y-m-d');
        $maxDate = Carbon::today()->format('Y-m-d');

        return [
            'user_id' => User::factory(),
            'food_name' => $this->faker->unique()->word,
            'eaten_at' => $this->faker->dateTimeBetween($minDate, $maxDate)->format('Y-m-d'),
            'proteins' => $this->faker->randomFloat(2, 0, 100),
            'fats' => $this->faker->randomFloat(2, 0, 100),
            'carbs' => $this->faker->randomFloat(2, 0, 100),
            'weight' => $this->faker->randomFloat(2, 0, 999),
        ];
    }
}
