<?php

namespace App\Http\Controllers;

use App\Services\FatSecretApiService;
use Illuminate\Http\Request;

class FatSecretApiController extends Controller
{
    public function __construct(
        private FatSecretApiService $apiService
    ) {}

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'page' => 'sometimes|integer|min:0',
            'max_results' => 'sometimes|integer|min:1|max:50'
        ]);

        try {
            $result = $this->apiService->searchFood(
                query: $request->input('query'),
                page: $request->input('page', 0),
                maxResults: $request->input('per_page', 20)
            );

            return response()->json([
                'success' => true,
                'data' => $result['items']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
