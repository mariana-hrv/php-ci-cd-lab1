<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModerationRecordResource;
use App\Models\Listing;
use App\Models\ModerationRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ModerationRecordApiController extends Controller
{
    #[OA\Get(
        path: '/moderation-records',
        summary: 'Отримати список записів модерації',
        description: 'Операція доступна лише адміністратору.',
        tags: ['ModerationRecords'],
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
                description: 'Список записів модерації з пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/ModerationRecord'
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
        $this->authorize('viewAny', ModerationRecord::class);

        $records = ModerationRecord::query()
            ->with(['listing.property', 'admin:id,name'])
            ->orderByDesc('id')
            ->paginate(10);

        return ModerationRecordResource::collection($records);
    }

    #[OA\Get(
        path: '/moderation-records/{moderationRecord}',
        summary: 'Переглянути окремий запис модерації',
        description: 'Операція доступна лише адміністратору.',
        tags: ['ModerationRecords'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'moderationRecord',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дані запису модерації',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ModerationRecord'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ дозволено лише адміністратору'),
            new OA\Response(response: 404, description: 'Запис модерації не знайдено'),
        ]
    )]
    public function show(
        ModerationRecord $moderationRecord
    ): ModerationRecordResource {
        $this->authorize('view', $moderationRecord);

        $moderationRecord->load([
            'listing.property',
            'admin:id,name',
        ]);

        return new ModerationRecordResource($moderationRecord);
    }

    #[OA\Post(
        path: '/moderation-records',
        summary: 'Модерувати оголошення',
        description: 'Адміністратор схвалює або відхиляє оголошення. Рішення модерації та зміна статусу оголошення зберігаються в одній транзакції.',
        tags: ['ModerationRecords'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['listing_id', 'decision'],
                properties: [
                    new OA\Property(
                        property: 'listing_id',
                        type: 'integer',
                        example: 11
                    ),
                    new OA\Property(
                        property: 'decision',
                        type: 'string',
                        enum: ['approved', 'rejected'],
                        example: 'approved'
                    ),
                    new OA\Property(
                        property: 'comment',
                        type: 'string',
                        nullable: true,
                        maxLength: 1000,
                        example: 'Оголошення відповідає вимогам.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Рішення модерації успішно збережено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ModerationRecord'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Доступ дозволено лише адміністратору'),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ModerationRecord::class);

        $validated = $request->validate([
            'listing_id' => [
                'required',
                'integer',
                'exists:listings,id',
            ],
            'decision' => [
                'required',
                'in:approved,rejected',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $listing = Listing::findOrFail(
            $validated['listing_id']
        );

        $record = DB::transaction(function () use (
            $listing,
            $validated,
            $request
        ) {
            $record = $listing->moderationRecords()->create([
                'admin_id' => $request->user()->id,
                'decision' => $validated['decision'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $listing->update([
                'status' => $validated['decision'] === 'approved'
                    ? 'published'
                    : 'draft',
            ]);

            return $record->load([
                'listing.property',
                'admin:id,name',
            ]);
        });

        return (new ModerationRecordResource($record))
            ->response()
            ->setStatusCode(201);
    }
}
