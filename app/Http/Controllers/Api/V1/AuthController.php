<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request){

    }

    public function login(LoginUserRequest $request){

    }

    public function logout() {

    }
}
