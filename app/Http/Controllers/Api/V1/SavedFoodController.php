<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSavedFoodRequest;
use App\Models\SavedFood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedFoodController extends Controller
{
    public function index(Request $request) {
        try{
            $perPage = min(max((int) $request->input('per_page', 10), 1), 20);

            $foods = SavedFood::where('user_id', auth() -> id())->paginate($perPage);

            return response()->json([
                'data' => $foods->items(),
                'meta' => [
                    'current_page' => $foods->currentPage(),
                    'per_page' => $foods->perPage(),
                    'total' => $foods->total(),
                    'last_page' => $foods->lastPage(),
                ]
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to retrieve data'],
                500);
        }
    }

    public function store(StoreSavedFoodRequest $request) {
        try {
            $food = SavedFood::create([
                'user_id' => auth() -> id(),
                'food_name' => $request->food_name,
                'proteins' => $request->proteins,
                'fats' => $request->fats,
                'carbs' => $request->carbs,
            ]);

            return response()->json([
                'message' => 'Food saved successfully',
                'data' => $food
            ], 201);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return response()->json([
                    'error' => 'Duplicate food',
                    'message' => 'A food with this name and nutritional values already exists.'
                ], 422);
            }

            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to save food'
            ], 500);
        }
    }

    public function destroy(SavedFood $savedFood) {
        try {
            if ($savedFood->user_id !== auth() -> id()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You do not have privileges to delete this resource.'
                ], 403);
            }

            $savedFood->delete();

            return response()->json([
                'message' => 'Food deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to delete resource.'
            ], 500);
        }
    }

    public function search(Request $request) {
        try{
            $perPage = min(max((int) $request->input('per_page', 10), 1), 20);

            $foods = SavedFood::where('food_name', 'LIKE', "%{$request->food_name}%")->paginate($perPage);

            return response()->json([
                'data' => $foods->items(),
                'meta' => [
                    'current_page' => $foods->currentPage(),
                    'per_page' => $foods->perPage(),
                    'total' => $foods->total(),
                    'last_page' => $foods->lastPage(),
                ]
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to retrieve data'],
                500);
        }
    }
}
