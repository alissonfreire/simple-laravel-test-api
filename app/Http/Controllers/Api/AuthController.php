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
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new user',
        description: 'Creates a new user account with the provided details.',
        tags: ['auth'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'johndoe@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User registered successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/User'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Form validation error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'fail'),
                new OA\Property(property: 'message', type: 'string', example: 'form validation error'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'email',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The email field is required.')
                        ),
                        new OA\Property(
                            property: 'password',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: [
                                'The password field confirmation does not match.',
                                'The password field must be at least 8 characters.',
                            ])
                        ),
                    ]
                ),
            ]
        )
    )]
    public function register(AuthRegisterRequest $request): ApiResponse
    {
        $userData = $request->only(['name', 'email', 'password']);

        $responseData = $this->createUserWithToken($userData);

        return ApiResponse::success(data: $responseData, statusCode: 201);
    }

    #[OA\Post(
        path: '/auth/login',
        summary: 'Login a existent user',
        description: 'Login a existent user account with the provided credentials.',
        tags: ['auth'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'johndoe@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'User successfully logged in',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                        new OA\Property(property: 'token', type: 'string', example: '1|mY3jSgSPNSCdPoeds7xydX1UyblNXYqh22wpjW8o2814f842'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, ref: '#/components/schemas/UnauthorizedResponse')]
    public function login(AuthLoginRequest $request): ApiResponse
    {
        $userData = $request->only(['name', 'email', 'password']);

        if (! $user = $this->maybeLogInUser($userData)) {
            return ApiResponse::fail(errorMessage: 'unauthorized error', statusCode: 401);
        }

        $responseData = $this->buildUserWithToken($user);

        return ApiResponse::success($responseData);
    }

    #[OA\Get(
        security: [['bearerAuth' => []]],
        path: '/auth/me',
        summary: 'Get logged in user details',
        description: 'Logs out the currently authenticated user.',
        tags: ['auth'],
    )]
    #[OA\Response(
        response: 200,
        description: 'User registered successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/User'),
                        new OA\Property(property: 'token', type: 'string', example: '1|mY3jSgSPNSCdPoeds7xydX1UyblNXYqh22wpjW8o2814f842'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, ref: '#/components/schemas/UnauthorizedResponse')]
    public function me(Request $request): ApiResponse
    {
        $responseData = ['user' => $request->user()];

        return ApiResponse::success($responseData);
    }

    #[OA\Delete(
        security: [['bearerAuth' => []]],
        path: '/auth/logout',
        summary: 'Logout a user',
        description: 'Logs out the currently authenticated user.',
        tags: ['auth'],
        responses: [
            new OA\Response(response: 204, description: 'No content'),
        ]
    )]
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
