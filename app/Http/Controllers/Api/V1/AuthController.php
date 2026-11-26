<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/register',
        summary: 'Реєстрація користувача',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'API Test User'),
                    new OA\Property(property: 'email', type: 'string', example: 'api-test@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'TestPassword123!'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'TestPassword123!'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Користувача зареєстровано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'email', type: 'string'),
                            ]
                        ),
                        new OA\Property(
                            property: 'token',
                            type: 'string',
                            example: '1|example-token'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Помилка валідації'),
            new OA\Response(response: 429, description: 'Забагато запитів'),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($validated);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
        ], 201);
    }

    #[OA\Post(
        path: '/login',
        summary: 'Вхід та отримання токена Sanctum',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'api-test@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'TestPassword123!'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успішна автентифікація',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'email', type: 'string'),
                            ]
                        ),
                        new OA\Property(
                            property: 'token',
                            type: 'string',
                            example: '1|example-token'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Помилка валідації'),
            new OA\Response(response: 429, description: 'Забагато запитів'),
            new OA\Response(response: 'default', description: 'Помилка автентифікації'),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неправильна електронна пошта або пароль.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
        ]);
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Вихід та відкликання поточного токена',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успішний вихід',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Вихід виконано успішно.',
        ]);
    }
}
