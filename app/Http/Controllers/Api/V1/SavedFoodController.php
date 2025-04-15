<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSavedFoodRequest;
use App\Http\Requests\UpdateSavedFoodRequest;
use App\Models\SavedFood;
use Illuminate\Http\Request;

class SavedFoodController extends Controller
{
    public function index(Request $request) {
        //
    }

    public function store(StoreSavedFoodRequest $request) {
        //
    }

    public function destroy(SavedFood $savedFood) {
        //
    }

    public function search(Request $request) {
        //
    }
}
