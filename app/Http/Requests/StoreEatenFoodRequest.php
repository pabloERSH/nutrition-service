<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEatenFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'weight' => ['numeric', 'min:0', 'max:99999.99'],
            'food_id' => ['nullable', 'exists:saved_foods,id'],
            'food_name' => ['string', 'max:255'],
            'proteins' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'fats' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'carbs' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $foodId = $this->input('food_id');
            $proteins = $this->input('proteins');
            $fats = $this->input('fats');
            $carbs = $this->input('carbs');
            $food_name = $this->input('food_name');

            if ($foodId && ($proteins !== null || $fats !== null || $carbs !== null || $food_name !== null)) {
                $validator->errors()->add('food_id', 'Cannot provide both food_id and nutrients.');
            } elseif (!$foodId && ($proteins === null || $fats === null || $carbs === null || $food_name === null)) {
                $validator->errors()->add('nutrients', 'Must provide all nutrients (proteins, fats, carbs) if food_id is not provided.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'food_name.required' => 'The food name is required.',
            'food_name.max' => 'The food name cannot exceed 255 characters.',
            'proteins.required' => 'Proteins are required.',
            'proteins.numeric' => 'Proteins must be a number.',
            'proteins.min' => 'Proteins cannot be negative.',
            'proteins.max' => 'Proteins cannot exceed 99.99.',
            'fats.required' => 'Fats are required.',
            'fats.numeric' => 'Fats must be a number.',
            'fats.min' => 'Fats cannot be negative.',
            'fats.max' => 'Fats cannot exceed 99.99.',
            'carbs.required' => 'Carbohydrates are required.',
            'carbs.numeric' => 'Carbohydrates must be a number.',
            'carbs.min' => 'Carbohydrates cannot be negative.',
            'carbs.max' => 'Carbohydrates cannot exceed 99.99.',
            'weight.required' => 'Weight is required.',
            'weight.numeric' => 'Weight must be a number.',
            'weight.min' => 'Weight cannot be negative.',
            'weight.max' => 'Weight cannot be exceed 99999.99.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => 'Validation failed',
            'message' => 'The provided data is invalid.',
            'errors' => $validator->errors()->all()
        ], 422));
    }
}
