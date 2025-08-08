<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\error;

class AuthController extends Controller
{

    // user registartion
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

    public function login(LoginRequest $request)
    {

        try {

            $credentials = $request->validated();

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid email or password"
                ], 401);
            }


            $user = Auth::user();

            event(new UserLoggedIn($user, $request->ip(), $request->userAgent()));

            $token = $user->createToken("api_token")->plainTextToken;


            return response()->json([
                "success" => true,
                "message" => "Login Successful.",
                "data" => [
                    "user" => $user,
                    "token" => $token
                ]

            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Login failed, please try again.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // logout
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                "success" => true,
                "message" => "Logged out successfully."
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Logout failed. Please try again.",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    public function logoutAll(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                "success" => true,
                "message" => "logged out from all devices.",
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Failed to loggout from all devices.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
