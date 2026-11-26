<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class UserApiController extends Controller
{
    #[OA\Get(
        path: '/users',
        summary: 'Отримати список користувачів',
        description: 'Операція доступна лише адміністратору.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список користувачів із пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/User'
                            )
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ дозволено лише адміністратору'),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->orderBy('id')
            ->paginate(10);

        return UserResource::collection($users);
    }

    #[OA\Get(
        path: '/users/{user}',
        summary: 'Переглянути окремого користувача',
        description: 'Операція доступна лише адміністратору.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 9
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дані користувача',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ дозволено лише адміністратору'),
            new OA\Response(response: 404, description: 'Користувача не знайдено'),
        ]
    )]
    public function show(User $user): UserResource
    {
        $this->authorize('viewAny', User::class);
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    #[OA\Get(
        path: '/profile',
        summary: 'Переглянути власний профіль',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дані автентифікованого користувача',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
        ]
    )]
    public function profile(Request $request): UserResource
    {
        $user = $request->user();

        $this->authorize('view', $user);

        return new UserResource($user);
    }

    #[OA\Put(
        path: '/profile',
        summary: 'Оновити власний профіль',
        description: 'Користувач може змінювати власне ім’я та електронну пошту. Зміна ролі не передбачена.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 255,
                        example: 'API Test User'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        maxLength: 255,
                        example: 'api-updated@example.com'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Профіль успішно оновлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
            new OA\Response(response: 422, description: 'Помилка валідації або електронна пошта вже використовується'),
        ]
    )]
    public function update(Request $request): UserResource
    {
        $user = $request->user();

        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return new UserResource($user);
    }

    #[OA\Delete(
        path: '/profile',
        summary: 'Видалити власний обліковий запис',
        description: 'Потребує підтвердження поточного пароля. Видалення адміністратора та користувача з пов’язаними даними заборонено.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password'],
                properties: [
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'TestPassword123!'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Обліковий запис успішно видалено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Обліковий запис успішно видалено.'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Видалення облікового запису заборонено'),
            new OA\Response(response: 409, description: 'Користувач має пов’язані записи'),
            new OA\Response(response: 422, description: 'Неправильний пароль або помилка валідації'),
        ]
    )]
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('delete', $user);

        $request->validate([
            'password' => ['required', 'current_password:sanctum'],
        ]);

        abort_if(
            $user->listings()->exists()
                || $user->viewingRequests()->exists()
                || $user->moderationRecords()->exists()
                || $user->favoriteListings()->exists()
                || $user->propertyNotifications()->exists(),
            409,
            'Неможливо видалити обліковий запис із пов’язаними даними.'
        );

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json([
            'message' => 'Обліковий запис успішно видалено.',
        ], 200);
    }
}
