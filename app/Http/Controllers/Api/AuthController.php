<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuthLoginRequest;
use App\Http\Requests\Api\AuthRegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $request): ApiResponse
    {
        $userData = $request->only(['name', 'email', 'password']);

        $responseData = $this->createUserWithToken($userData);

        return ApiResponse::success(data: $responseData, statusCode: 201);
    }

    public function login(AuthLoginRequest $request): ApiResponse
    {
        $userData = $request->only(['name', 'email', 'password']);

        if (! $user = $this->maybeLogInUser($userData)) {
            return ApiResponse::fail(errorMessage: 'unauthorized error', statusCode: 401);
        }

        $responseData = $this->buildUserWithToken($user);

        return ApiResponse::success($responseData);
    }

    public function me(Request $request): ApiResponse
    {
        $responseData = ['user' => $request->user()];

        return ApiResponse::success($responseData);
    }

    public function logout(Request $request): Response
    {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }

    private function maybeLogInUser(array $userData): User|false
    {
        $user = User::where('email', $userData['email'])->first();

        // necessary to prevent user enumeration attack
        $password = $user?->password ?? config('app.wrong_user_password');

        $validPaswd = Hash::check($userData['password'], $password);

        if (! $user || ! $validPaswd) {
            return false;
        }

        return $user;
    }

    private function createUserWithToken(array $userData): array
    {
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        return $this->buildUserWithToken($user);
    }

    private function buildUserWithToken(User $user): array
    {
        $token = $this->createUserToken($user);

        return [
            'user' => $user->toArray(),
            'token' => $token,
        ];
    }

    private function createUserToken(User $user): string
    {
        return $user->createToken($user->name.'-AuthToken')->plainTextToken;
    }
}
