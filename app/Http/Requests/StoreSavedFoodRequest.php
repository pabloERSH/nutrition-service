<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavedFoodRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'food_name' => ['required', 'string', 'max:255'],
            'proteins' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'fats' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'carbs' => ['required', 'numeric', 'min:0', 'max:999.99'],
        ];
    }
}
