<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

            $refreshToken = Str::random(64);

            RefreshToken::create([
                "user_id" => $user->id,
                "token" => hash('sha256', $refreshToken),
                "expires_at" => Carbon::now()->addDays(7)
            ]);

            return response()->json([
                "success" => true,
                "message" => "Login Successful.",
                "data" => [
                    "user" => $user,
                    "token" => $token,
                    "refresh_token" => $refreshToken
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
            $user = $request->user();
            $request->user()->currentAccessToken()->delete();
            RefreshToken::where('user_id', $user->id)->delete();
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


    // FORGET PASSWORD
    public function forgotPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email|exists:users,email"
        ]);

        try {

            $status = Password::sendResetLink(
                $request->only("email")
            );

            return $status == Password::RESET_LINK_SENT
                ? response()->json([
                    "success" => true,
                    "message" => $status
                ], 200)
                :
                response()->json([
                    "success" => false,
                    "message" => $status
                ], 400);
        } catch (Exception $e) {
            response()->json([
                "success" => false,
                "message" => "Failed to send reset link.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // RESET PASSWORD
    public function resetPassword(Request $request)
    {
        $request->validate([
            "token" => "required",
            "email" => "required|email|exists:users,email",
            "password" => "required|string|min:5|confirmed"
        ]);

        try {
            $status = Password::reset(
                $request->only("email", "password", "password_confirmation", "token"),
                function ($user, $password) {
                    $user->forceFill([
                        "password" => Hash::make($password),
                        "remember_token" => Str::random(60),
                    ])->save();
                    event(new PasswordReset($user));
                }
            );

            return $status == Password::PASSWORD_RESET
                ?
                response()->json([
                    "success" => true,
                    "message" => __($status)
                ], 200)
                :
                response()->json([
                    "success" => false,
                    "message" => __($status)
                ], 400);
        } catch (Exception $e) {
            response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }


    public function refreshToken(Request $request)
    {
        $request->validate([
            "refresh_token" => "required"
        ]);


        try {
            $hashToken = hash("sha256", $request->refreshToken);
            $storedToken = RefreshToken::where("token", $hashToken)->where("expires_at", ">", now())->first();

            if (!$storedToken) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid or expires refresh token"
                ], 401);
            }

            $user = $storedToken->user();

            // DELETE OLD TOKENS
            $user->tokens()->delete();

            // Create new access token
            $newAccessToken = $user->createToken("api_token")->plainTextToken;


            return response()->json([
                'token' => $newAccessToken,
                'token_type' => 'Bearer',
                'expires_in' => 15 * 60,
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
