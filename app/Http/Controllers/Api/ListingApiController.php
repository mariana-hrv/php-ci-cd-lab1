<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ListingApiController extends Controller
{
    #[OA\Get(
        path: '/listings',
        summary: 'Отримати список оголошень',
        tags: ['Listings'],
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
                description: 'Список оголошень із пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/Listing'
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
        $this->authorize('viewAny', Listing::class);

        $listings = Listing::query()
            ->with(['property', 'seller:id,name'])
            ->orderByDesc('id')
            ->paginate(10);

        return ListingResource::collection($listings);
    }

    #[OA\Get(
        path: '/listings/{listing}',
        summary: 'Переглянути окреме оголошення',
        tags: ['Listings'],
        parameters: [
            new OA\Parameter(
                name: 'listing',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 11
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дані оголошення',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Listing'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Оголошення не знайдено'),
        ]
    )]
    public function show(Listing $listing): ListingResource
    {
        $this->authorize('view', $listing);

        $listing->load(['property', 'seller:id,name']);

        return new ListingResource($listing);
    }

    #[OA\Post(
        path: '/listings',
        summary: 'Створити оголошення',
        description: 'Власником автоматично стає автентифікований користувач.',
        tags: ['Listings'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['property_id', 'price'],
                properties: [
                    new OA\Property(
                        property: 'property_id',
                        type: 'integer',
                        example: 6
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        example: 2500000
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Тестове оголошення через Swagger'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['draft', 'published', 'archived'],
                        example: 'draft'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Оголошення успішно створено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Listing'
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
        $this->authorize('create', Listing::class);

        $validated = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'price' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in([
                'draft',
                'published',
                'archived',
            ])],
        ]);

        $validated['seller_id'] = $request->user()->id;

        $listing = Listing::create($validated);

        $listing->refresh();
        $listing->load(['property', 'seller:id,name']);

        return (new ListingResource($listing))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/listings/{listing}',
        summary: 'Оновити оголошення',
        description: 'Редагувати оголошення може лише його власник.',
        tags: ['Listings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'listing',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 11
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        example: 2700000
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Оновлено через Swagger'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['draft', 'published', 'archived'],
                        example: 'published'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Оголошення успішно оновлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Listing'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є власником оголошення'),
            new OA\Response(response: 404, description: 'Оголошення не знайдено'),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function update(
        Request $request,
        Listing $listing
    ): ListingResource {
        $this->authorize('update', $listing);

        $validated = $request->validate([
            'price' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in([
                'draft',
                'published',
                'archived',
            ])],
        ]);

        $listing->update($validated);

        $listing->load(['property', 'seller:id,name']);

        return new ListingResource($listing);
    }

    #[OA\Delete(
        path: '/listings/{listing}',
        summary: 'Видалити оголошення',
        description: 'Видаляти оголошення може лише його власник.',
        tags: ['Listings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'listing',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 11
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Оголошення успішно видалено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Оголошення успішно видалено.'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є власником оголошення'),
            new OA\Response(response: 404, description: 'Оголошення не знайдено'),
        ]
    )]
    public function destroy(Listing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);

        $listing->delete();

        return response()->json([
            'message' => 'Оголошення успішно видалено.',
        ], 200);
    }
}
