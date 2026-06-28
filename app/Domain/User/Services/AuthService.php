<?php

namespace App\Domain\User\Services;

use App\Core\Exceptions\ApiException;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'user',
        ]);
    }

    public function login(array $credentials): string
    {
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            throw new ApiException('Invalid credentials. بيانات الدخول غير صحيحة.', 401);
        }

        return $token;
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function refresh(): string
    {
        return JWTAuth::refresh(JWTAuth::getToken());
    }

    public function user(): ?User
    {
        return Auth::guard('api')->user();
    }
}
