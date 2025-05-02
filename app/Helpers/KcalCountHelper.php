<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class KcalCountHelper
{
    public static function addKcal(LengthAwarePaginator $foods): LengthAwarePaginator
    {
        $foods->getCollection()->transform(function ($food) {
            $food->kcal = round(($food->proteins * 4) + ($food->fats * 9) + ($food->carbs * 4), 2);
            return $food;
        });
        return $foods;
    }

    public static function addKcalWeight(LengthAwarePaginator $foods): LengthAwarePaginator
    {
        $foods->getCollection()->transform(function ($food) {
            $food->kcal = round((($food->weight) / 100) * (($food->proteins * 4) + ($food->fats * 9) + ($food->carbs * 4)), 2);
            return $food;
        });
        return $foods;
    }
}
