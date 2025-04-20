<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateSavedFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'food_name' => ['sometimes', 'string', 'max:255'],
            'proteins' => ['sometimes', 'numeric', 'min:0', 'max:99.99'],
            'fats' => ['sometimes', 'numeric', 'min:0', 'max:99.99'],
            'carbs' => ['sometimes', 'numeric', 'min:0', 'max:99.99'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $proteins = $this->input('proteins', $this->route('savedFood')?->proteins ?? 0);
            $fats = $this->input('fats', $this->route('savedFood')?->fats ?? 0);
            $carbs = $this->input('carbs', $this->route('savedFood')?->carbs ?? 0);

            if ($proteins + $fats + $carbs > 100) {
                $validator->errors()->add('nutrients', 'The total amount of nutrients should be less than or equal to 100 grams.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'food_name.max' => 'The food name cannot exceed 255 characters.',
            'food_name.unique' => 'A food with these nutritional values already exists.',
            'proteins.numeric' => 'Proteins must be a number.',
            'proteins.min' => 'Proteins cannot be negative.',
            'proteins.max' => 'Proteins cannot exceed 99.99.',
            'fats.numeric' => 'Fats must be a number.',
            'fats.min' => 'Fats cannot be negative.',
            'fats.max' => 'Fats cannot exceed 99.99.',
            'carbs.numeric' => 'Carbohydrates must be a number.',
            'carbs.min' => 'Carbohydrates cannot be negative.',
            'carbs.max' => 'Carbohydrates cannot exceed 99.99.',
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
