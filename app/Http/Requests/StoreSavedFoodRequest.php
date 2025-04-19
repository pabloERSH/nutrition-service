<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSavedFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'food_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('saved_foods')->where(function ($query) {
                    return $query->where('proteins', $this->proteins)
                        ->where('fats', $this->fats)
                        ->where('carbs', $this->carbs);
                }),
            ],
            'proteins' => ['required', 'numeric', 'min:0', 'max:99.99'],
            'fats' => ['required', 'numeric', 'min:0', 'max:99.99'],
            'carbs' => ['required', 'numeric', 'min:0', 'max:99.99'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $proteins = $this->input('proteins');
            $fats = $this->input('fats');
            $carbs = $this->input('carbs');

            if ($proteins + $fats + $carbs > 100) {
                $validator->errors()->add('nutrients', 'The total amount of nutrients should be less than or equal to 100 grams.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'food_name.required' => 'The food name is required.',
            'food_name.max' => 'The food name cannot exceed 255 characters.',
            'food_name.unique' => 'A food with these nutritional values already exists.',
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
