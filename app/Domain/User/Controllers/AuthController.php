<?php

namespace App\Domain\User\Controllers;

use App\Core\Base\BaseController;
use App\Domain\User\Requests\LoginRequest;
use App\Domain\User\Requests\RegisterRequest;
use App\Domain\User\Resources\UserResource;
use App\Domain\User\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    protected AuthService $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->service->register(array_merge($request->validated(), [
            'role' => 'user',
        ]));

        $token = JWTAuth::fromUser($user);

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 'Registered successfully.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->service->login($request->only('email', 'password'));

        return $this->successResponse([
            'user' => new UserResource(Auth::guard('api')->user()),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 'Logged in successfully.');
    }

    public function logout(): JsonResponse
    {
        $this->service->logout();

        return $this->successResponse(null, 'Logged out successfully.');
    }

    public function refresh(): JsonResponse
    {
        $token = $this->service->refresh();

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 'Token refreshed successfully.');
    }

    public function me(): JsonResponse
    {
        return $this->successResponse(new UserResource($this->service->user()), 'Authenticated user.');
    }
}
