<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\RegisterCourierRequest;
use App\Http\Requests\Api\Auth\RegisterRestaurantRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Domain\User\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::created([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registration successful');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 403);
        }

        return ApiResponse::success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = $this->authService->forgotPassword($request->email);

        if ($status === Password::RESET_LINK_SENT) {
            return ApiResponse::success(null, 'Password reset link sent.');
        }

        return ApiResponse::error('Unable to send reset link. Check your email address.', 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = $this->authService->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token')
        );

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success(null, 'Password has been reset.');
        }

        return ApiResponse::error('Invalid or expired reset token.', 422);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->authService->changePassword(
                $request->user(),
                $request->current_password,
                $request->password,
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success(null, 'Password changed successfully.');
    }

    public function registerRestaurant(RegisterRestaurantRequest $request): JsonResponse
    {
        $result = $this->authService->registerRestaurant($request->validated());

        return ApiResponse::created([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Restaurant owner registration successful. Your application is pending approval.');
    }

    public function registerCourier(RegisterCourierRequest $request): JsonResponse
    {
        $result = $this->authService->registerCourier($request->validated());

        return ApiResponse::created([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Courier registration successful. Your application is pending approval.');
    }

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        try {
            $user = $this->authService->verifyEmail($id, $hash);
        } catch (\RuntimeException $e) {
            $code = str_contains($e->getMessage(), 'not found') ? 404 : 403;
            return ApiResponse::error($e->getMessage(), $code);
        }

        return ApiResponse::success(null, $user->hasVerifiedEmail() ? 'Email verified successfully.' : 'Email already verified.');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $this->authService->resendVerification($request->email);

        // Don't reveal whether the email exists
        return ApiResponse::success(null, 'If this email exists, a verification link has been sent.');
    }
}
