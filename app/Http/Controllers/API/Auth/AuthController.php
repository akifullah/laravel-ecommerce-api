<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        try {
            $data = $request->validated();

            $user = User::create([
                "name" => $data["name"],
                "email" => $data["email"],
                "password" => Hash::make($data["password"]),
            ]);

            // Fire event to track IP/device
            event(new UserLoggedIn($user, $request->ip(), $request->userAgent()));

            $token = $user->createToken("api_token")->plainTextToken;

            return response()->json([
                "success" => true,
                "message" => "Registration Successful.",
                "data" => [
                    "user" => $user,
                    "token" => $token
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Registration failed.",
                "error" => $e->getMessage(),
            ], 500);
        }
    }
}
