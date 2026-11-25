<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyPhotoResource;
use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class PropertyPhotoApiController extends Controller
{
    #[OA\Get(
        path: '/properties/{property}/photos',
        summary: 'Отримати фотографії об’єкта нерухомості',
        tags: ['PropertyPhotos'],
        parameters: [
            new OA\Parameter(
                name: 'property',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 6
            ),
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
                description: 'Список фотографій із пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/PropertyPhoto'
                            )
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Об’єкт нерухомості не знайдено'),
        ]
    )]
    public function index(Property $property): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PropertyPhoto::class);

        $photos = $property->photos()
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->paginate(10);

        return PropertyPhotoResource::collection($photos);
    }

    #[OA\Get(
        path: '/photos/{photo}',
        summary: 'Переглянути окрему фотографію',
        tags: ['PropertyPhotos'],
        parameters: [
            new OA\Parameter(
                name: 'photo',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 11
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дані фотографії',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/PropertyPhoto'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Фотографію не знайдено'),
        ]
    )]
    public function show(PropertyPhoto $photo): PropertyPhotoResource
    {
        $this->authorize('view', $photo);

        return new PropertyPhotoResource($photo);
    }

    #[OA\Post(
        path: '/photos',
        summary: 'Додати фотографію нерухомості',
        description: 'Додавати фотографії може продавець, який має оголошення для відповідного об’єкта нерухомості.',
        tags: ['PropertyPhotos'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['property_id', 'file_path'],
                properties: [
                    new OA\Property(
                        property: 'property_id',
                        type: 'integer',
                        example: 6
                    ),
                    new OA\Property(
                        property: 'file_path',
                        type: 'string',
                        maxLength: 255,
                        example: 'properties/swagger-photo.jpg'
                    ),
                    new OA\Property(
                        property: 'is_primary',
                        type: 'boolean',
                        default: false,
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Фотографію успішно додано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/PropertyPhoto'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не має права додавати фотографії цього об’єкта'),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'file_path' => ['required', 'string', 'max:255'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $property = Property::findOrFail($validated['property_id']);

        $this->authorize('create', [PropertyPhoto::class, $property]);

        $photo = $property->photos()->create([
            'file_path' => $validated['file_path'],
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return (new PropertyPhotoResource($photo))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Delete(
        path: '/photos/{photo}',
        summary: 'Видалити фотографію нерухомості',
        description: 'Видалення доступне продавцю, який має оголошення для відповідного об’єкта нерухомості.',
        tags: ['PropertyPhotos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'photo',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 11
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Фотографію успішно видалено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Фотографію успішно видалено.'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
            new OA\Response(response: 404, description: 'Фотографію не знайдено'),
        ]
    )]
    public function destroy(PropertyPhoto $photo): JsonResponse
    {
        $photo->load('property');

        $this->authorize('delete', $photo);

        $photo->delete();

        return response()->json([
            'message' => 'Фотографію успішно видалено.',
        ], 200);
    }
}
