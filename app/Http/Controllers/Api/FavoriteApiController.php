<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class FavoriteApiController extends Controller
{
    #[OA\Get(
        path: '/favorites',
        summary: 'Отримати власні вибрані оголошення',
        description: 'Повертає лише вибрані оголошення автентифікованого користувача.',
        tags: ['Favorites'],
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
                description: 'Список вибраних оголошень із пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/Favorite'
                            )
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Favorite::class);

        $favorites = $request->user()->favorites()
            ->with(['listing.property', 'listing.seller:id,name'])
            ->orderByDesc('id')
            ->paginate(10);

        return FavoriteResource::collection($favorites);
    }

    #[OA\Put(
        path: '/favorites',
        summary: 'Синхронізувати вибрані оголошення',
        description: 'Замінює власний список вибраного переданим масивом ідентифікаторів. Порожній масив очищує список.',
        tags: ['Favorites'],
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['listing_ids'],
                properties: [
                    new OA\Property(
                        property: 'listing_ids',
                        type: 'array',
                        uniqueItems: true,
                        items: new OA\Items(type: 'integer'),
                        example: [1, 5, 11]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список вибраних оголошень успішно оновлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/Favorite'
                            )
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function sync(Request $request): AnonymousResourceCollection
    {
        $this->authorize('sync', Favorite::class);

        $validated = $request->validate([
            'listing_ids' => ['present', 'array'],
            'listing_ids.*' => [
                'integer',
                'distinct',
                'exists:listings,id',
            ],
        ]);

        $request->user()->favoriteListings()->sync(
            $validated['listing_ids']
        );

        return $this->index($request);
    }
}
