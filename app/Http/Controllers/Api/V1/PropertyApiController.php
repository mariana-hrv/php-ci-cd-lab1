<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class PropertyApiController extends Controller
{
    #[OA\Get(
        path: '/properties',
        summary: 'Отримати список об’єктів нерухомості',
        tags: ['Properties'],
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
                description: 'Список нерухомості з пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/Property'
                            )
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Property::class);

        $properties = Property::query()
            ->with([
                'listings' => fn ($query) => $query->visibleTo(request()->user('sanctum')),
                'photos',
            ])
            ->orderByDesc('id')
            ->paginate(10);

        return PropertyResource::collection($properties);
    }

    #[OA\Get(
        path: '/properties/{property}',
        summary: 'Переглянути окремий об’єкт нерухомості',
        tags: ['Properties'],
        parameters: [
            new OA\Parameter(
                name: 'property',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 6
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дані об’єкта нерухомості',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Property'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Об’єкт не знайдено'),
        ]
    )]
    public function show(Property $property): PropertyResource
    {
        $this->authorize('view', $property);

        $property->load([
            'listings' => fn ($query) => $query->visibleTo(request()->user('sanctum')),
            'photos',
        ]);

        return new PropertyResource($property);
    }

    #[OA\Post(
        path: '/properties',
        summary: 'Створити об’єкт нерухомості',
        tags: ['Properties'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['property_type', 'city', 'address', 'area', 'rooms'],
                properties: [
                    new OA\Property(
                        property: 'property_type',
                        type: 'string',
                        minLength: 2,
                        maxLength: 100,
                        example: 'Квартира'
                    ),
                    new OA\Property(
                        property: 'city',
                        type: 'string',
                        minLength: 2,
                        maxLength: 100,
                        example: 'Івано-Франківськ'
                    ),
                    new OA\Property(
                        property: 'address',
                        type: 'string',
                        minLength: 5,
                        maxLength: 255,
                        example: 'вул. Галицька, 15'
                    ),
                    new OA\Property(
                        property: 'area',
                        type: 'number',
                        example: 75.5
                    ),
                    new OA\Property(
                        property: 'rooms',
                        type: 'integer',
                        minimum: 1,
                        example: 3
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Об’єкт нерухомості створено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Property'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Property::class);

        $validated = $request->validate([
            'property_type' => ['required', 'string', 'min:2', 'max:100'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'area' => ['required', 'numeric', 'gt:0'],
            'rooms' => ['required', 'integer', 'min:1'],
        ]);

        $property = Property::create($validated);

        $property->load([
            'listings' => fn ($query) => $query->visibleTo(request()->user('sanctum')),
            'photos',
        ]);

        return (new PropertyResource($property))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/properties/{property}',
        summary: 'Оновити об’єкт нерухомості',
        description: 'Редагування доступне лише адміністратору.',
        tags: ['Properties'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'property',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 6
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['property_type', 'city', 'address', 'area', 'rooms'],
                properties: [
                    new OA\Property(
                        property: 'property_type',
                        type: 'string',
                        minLength: 2,
                        maxLength: 100,
                        example: 'Квартира'
                    ),
                    new OA\Property(
                        property: 'city',
                        type: 'string',
                        minLength: 2,
                        maxLength: 100,
                        example: 'Івано-Франківськ'
                    ),
                    new OA\Property(
                        property: 'address',
                        type: 'string',
                        minLength: 5,
                        maxLength: 255,
                        example: 'вул. Галицька, 20'
                    ),
                    new OA\Property(
                        property: 'area',
                        type: 'number',
                        example: 80.5
                    ),
                    new OA\Property(
                        property: 'rooms',
                        type: 'integer',
                        minimum: 1,
                        example: 3
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Об’єкт нерухомості оновлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Property'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ дозволено лише адміністратору'),
            new OA\Response(response: 404, description: 'Об’єкт не знайдено'),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function update(
        Request $request,
        Property $property
    ): PropertyResource {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'property_type' => ['required', 'string', 'min:2', 'max:100'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'area' => ['required', 'numeric', 'gt:0'],
            'rooms' => ['required', 'integer', 'min:1'],
        ]);

        $property->update($validated);

        $property->load([
            'listings' => fn ($query) => $query->visibleTo(request()->user('sanctum')),
            'photos',
        ]);

        return new PropertyResource($property);
    }

    #[OA\Delete(
        path: '/properties/{property}',
        summary: 'Видалити об’єкт нерухомості',
        description: 'Видалення доступне адміністратору за відсутності пов’язаних оголошень і фотографій.',
        tags: ['Properties'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'property',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 6
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Об’єкт нерухомості видалено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Об’єкт нерухомості успішно видалено.'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ дозволено лише адміністратору'),
            new OA\Response(response: 404, description: 'Об’єкт не знайдено'),
            new OA\Response(response: 409, description: 'Об’єкт містить пов’язані записи'),
        ]
    )]
    public function destroy(Property $property): JsonResponse
    {
        $this->authorize('delete', $property);

        abort_if(
            $property->listings()->exists()
                || $property->photos()->exists(),
            409,
            'Неможливо видалити об’єкт із пов’язаними записами.'
        );

        $property->delete();

        return response()->json([
            'message' => 'Об’єкт нерухомості успішно видалено.',
        ], 200);
    }
}
