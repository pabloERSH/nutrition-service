<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSavedFoodRequest;
use App\Http\Requests\UpdateSavedFoodRequest;
use App\Models\SavedFood;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\KcalCountHelper;

class SavedFoodController extends Controller
{
    public function index(Request $request): JsonResponse {
        try{
            $perPage = min(max((int) $request->input('per_page', 10), 1), 20);

            $foods = SavedFood::where('user_id', auth() -> id())->paginate($perPage);
            $foods = KcalCountHelper::addKcal($foods);
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

    public function store(StoreSavedFoodRequest $request): JsonResponse {
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
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to update food'
            ], 500);
        }
    }

    public function destroy(SavedFood $savedFood): JsonResponse {
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

    public function search(Request $request): JsonResponse {
        try{
            $perPage = min(max((int) $request->input('per_page', 10), 1), 20);

            $foods = SavedFood::where('food_name', 'LIKE', "%{$request->food_name}%")->paginate($perPage);
            $foods = KcalCountHelper::addKcal($foods);

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

    public function update(UpdateSavedFoodRequest $request, SavedFood $savedFood): JsonResponse {
        try {
            if ($savedFood->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You do not have privileges to update this resource.'
                ], 403);
            }

            $savedFood->update($request->validated());

            return response()->json([
                'message' => 'Food updated successfully',
                'data' => $savedFood->fresh()
            ], 200);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return response()->json([
                    "error" => "Validation failed",
                    "message" => "The provided data is invalid.",
                    "errors" => [
                        "A food with these nutritional values already exists."
                    ]
                ], 422);
            }

            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to update food'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to update food'
            ], 500);
        }
    }
}
