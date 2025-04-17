<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEatenFoodRequest;
use App\Models\EatenFood;
use App\Models\SavedFood;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

function add_kcal_weight($foods) {
    $foods->getCollection()->transform(function ($food) {
        $food->kcal = round(((float)($food->weight)/100) * (($food->proteins * 4) + ($food->fats * 9) + ($food->carbs * 4)), 2);
        return $food;
    });
    return $foods;
}

class EatenFoodController extends Controller
{
    public function destroy(EatenFood $eatenFood): JsonResponse {
        try {
            if ($eatenFood->user_id !== auth() -> id()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You do not have privileges to delete this resource.'
                ], 403);
            }

            $eatenFood->delete();

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

    public function store(StoreEatenFoodRequest $request): JsonResponse {
        try{
            $food = EatenFood::create([
                'user_id' => auth() -> id(),
                'food_name' => $request->food_name,
                'weight' => $request->weight,
                'food_id' => $request->food_id,
                'proteins' => $request->proteins,
                'fats' => $request->fats,
                'carbs' => $request->carbs,
            ]);

            return response()->json([
                'message' => 'Food saved successfully',
                'data' => $food
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to save food'
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse {
        try{
            $perPage = min(max((int) $request->input('per_page', 10), 1), 20);

            $foods = EatenFood::where('user_id', auth() -> id())->paginate($perPage);

            $foods->getCollection()->transform(function ($eatenFood) {
                if (!is_null($eatenFood->food_id)) {
                    $savedFood = SavedFood::find($eatenFood->food_id);

                    if ($savedFood) {
                        $eatenFood->food_name = $savedFood->food_name;
                        $eatenFood->proteins = $savedFood->proteins;
                        $eatenFood->fats = $savedFood->fats;
                        $eatenFood->carbs = $savedFood->carbs;
                    }
                }
                unset($eatenFood->food_id);
                return $eatenFood;
            });

            $foods = add_kcal_weight($foods);

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

    public function update(StoreEatenFoodRequest $request, eatenFood $eatenFood): JsonResponse {
        try {
            if ($eatenFood->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You do not have privileges to update this resource.'
                ], 403);
            }

            $eatenFood->update([
                'food_name' => $request->food_name,
                'weight' => $request->weight,
                'proteins' => $request->proteins,
                'fats' => $request->fats,
                'carbs' => $request->carbs,
            ]);

            return response()->json([
                'message' => 'Food updated successfully',
            ], 200);

        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return response()->json([
                    'error' => 'Duplicate food',
                    'message' => 'A food with this name and nutritional values already exists.'
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

    public function showByDate(Request $request): JsonResponse {
        try{
            $date = $request->date;
            $perPage = min(max((int) $request->input('per_page', 10), 1), 20);
            if (strtotime($date) === false) {
                return response()->json([
                    'error' => 'Date not valid',
                    'message' => 'Date should be in format YYYY-MM-DD'
                ], 422);
            }
            $foods = EatenFood::whereDate('created_at', $date)->paginate($perPage);
            $foods = add_kcal_weight($foods);

            $totalKcal = 0;
            $totalProteins = 0;
            $totalFats = 0;
            $totalCarbs = 0;

            foreach ($foods as $food) {
                $totalKcal += $food->kcal;
                $totalProteins += $food->proteins;
                $totalFats += $food->fats;
                $totalCarbs += $food->carbs;
            }

            return response()->json([
                'data' => [
                    $foods->items(),
                    'Total proteins' => $totalProteins,
                    'Total fats' => $totalFats,
                    'Total carbs' => $totalCarbs,
                    'Total kcal' => $totalKcal,
                ],
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
                'message' => 'Failed to retrieve data'
            ], 500);
        }
    }
}
