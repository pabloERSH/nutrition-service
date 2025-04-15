<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEatenFoodRequest;
use App\Models\EatenFood;
use Illuminate\Http\Request;

class EatenFoodController extends Controller
{
    public function destroy(EatenFood $eatenFood) {
        //
    }

    public function store(StoreEatenFoodRequest $request) {
        //
    }

    public function showbydate(Request $request) {
        //
    }

    public function storebysearch(Request $request) {
        //
    }
}
