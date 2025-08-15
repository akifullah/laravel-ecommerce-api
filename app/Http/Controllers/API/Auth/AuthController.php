<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\RefreshToken;
use App\Models\User;
use App\Traits\ApiResponse;
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
    use ApiResponse;
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

            $responseData = [
                "message" => "Registration Successful.",
                "data" =>  $user
            ];

            return $this->successResponse($responseData, 201);
        } catch (Exception $e) {
            $responseData = [
                "message" => "Registration failed.",
                "error" => $e->getMessage()
            ];
            return $this->errorResponse($responseData, 500);
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


            $responseData = [
                "message" => "Login Successful.",
                "data" => [
                    "user" => $user,
                    "token" => $token,
                    "refresh_token" => $refreshToken
                ]
            ];

            return $this->successResponse($responseData, 200);
        } catch (Exception $e) {
            $responseData = [
                "message" => "Login failed, please try again.",
                "error" => $e->getMessage()
            ];
            return $this->errorResponse($responseData, 500);
        }
    }

    // logout
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $request->user()->currentAccessToken()->delete();
            RefreshToken::where('user_id', $user->id)->delete();

            $responseData = [
                "message" => "Logged out successfully."
            ];

            return $this->successResponse($responseData, 200);
        } catch (Exception $e) {

            $responseData = [
                "message" => "Logout failed. Please try again.",
                "error" => $e->getMessage()
            ];
            return $this->errorResponse($responseData, 500);
        }
    }


    public function logoutAll(Request $request)
    {
        try {
            $request->user()->tokens()->delete();


            $responseData = [
                "message" => "logged out from all devices.",
            ];

            return $this->successResponse($responseData, 200);
        } catch (Exception $e) {

            $responseData = [
                "message" => "Failed to loggout from all devices.",
                "error" => $e->getMessage()
            ];
            return $this->errorResponse($responseData, 500);
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

            if ($status == Password::RESET_LINK_SENT) {
                $responseData = ["message" => __($status)];
                return $this->successResponse($responseData, 200);
            } else {
                $responseData = [
                    "message" => __($status)
                ];
                return $this->errorResponse($responseData, 400);
            }
        } catch (Exception $e) {

            $responseData = [
                "message" => "Failed to send reset link.",
                "error" => $e->getMessage()
            ];
            return $this->errorResponse($responseData, 500);
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

            if ($status == Password::PASSWORD_RESET) {
                $responseData = ["message" => __($status)];
                return $this->successResponse($responseData, 200);
            } else {
                $responseData = [
                    "message" => __($status)
                ];
                return $this->errorResponse($responseData, 400);
            }
        } catch (Exception $e) {
            $responseData = [
                "message" => $e->getMessage()
            ];
            return $this->errorResponse($responseData, 500);
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

                $responseData = [
                   "message" => "Invalid or expires refresh token"
                ];
                return $this->errorResponse($responseData, 401);

               
            }

            $user = $storedToken->user();

            // DELETE OLD TOKENS
            $user->tokens()->delete();

            // Create new access token
            $newAccessToken = $user->createToken("api_token")->plainTextToken;


            $responseData = [
                "message" => "Refresh token.",
                "data" => [
                    'token' => $newAccessToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 15 * 60,
                ]
            ];

            return $this->successResponse($responseData, 200);
        } catch (Exception $e) {

            $responseData = [
                "message" => $e->getMessage()
            ];
            return $this->errorResponse($responseData, 500);
        }
    }
}
