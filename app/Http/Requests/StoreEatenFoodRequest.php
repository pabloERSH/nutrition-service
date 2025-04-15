<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEatenFoodRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'weight' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'food_id' => ['nullable', 'exists:saved_foods,id'],
            'proteins' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'fats' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'carbs' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $foodId = $this->input('food_id');
            $proteins = $this->input('proteins');
            $fats = $this->input('fats');
            $carbs = $this->input('carbs');

            if ($foodId && ($proteins !== null || $fats !== null || $carbs !== null)) {
                $validator->errors()->add('food_id', 'Cannot provide both food_id and nutrients.');
            } elseif (!$foodId && ($proteins === null || $fats === null || $carbs === null)) {
                $validator->errors()->add('nutrients', 'Must provide all nutrients (proteins, fats, carbs) if food_id is not provided.');
            }
        });
    }
}
