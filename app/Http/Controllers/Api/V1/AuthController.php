<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request){
        return User::create($request->all());
    }

    public function login(LoginUserRequest $request){
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Wrong email or password!'], 401);
        }else{
            $user = User::query()->where('email', $request->email)->first();
            $user->tokens()->delete();
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken("Token of {$user->name}")->plainTextToken,
            ]);
        }
    }

    public function logout() {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out, token removed'
        ]);
    }
}
