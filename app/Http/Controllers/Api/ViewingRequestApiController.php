<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ViewingRequestResource;
use App\Models\ViewingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ViewingRequestApiController extends Controller
{
    #[OA\Get(
        path: '/viewing-requests',
        summary: 'Отримати власні заявки на перегляд',
        tags: ['ViewingRequests'],
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
                description: 'Список власних заявок із пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/ViewingRequest'
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
        $this->authorize('viewAny', ViewingRequest::class);

        $requests = $request->user()
            ->viewingRequests()
            ->with(['listing.property', 'listing.seller:id,name'])
            ->orderByDesc('id')
            ->paginate(10);

        return ViewingRequestResource::collection($requests);
    }

    #[OA\Get(
        path: '/viewing-requests/{viewingRequest}',
        summary: 'Переглянути власну заявку',
        tags: ['ViewingRequests'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'viewingRequest',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дані заявки',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ViewingRequest'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є власником заявки'),
            new OA\Response(response: 404, description: 'Заявку не знайдено'),
        ]
    )]
    public function show(ViewingRequest $viewingRequest): ViewingRequestResource
    {
        $this->authorize('view', $viewingRequest);

        $viewingRequest->load([
            'listing.property',
            'listing.seller:id,name',
        ]);

        return new ViewingRequestResource($viewingRequest);
    }

    #[OA\Post(
        path: '/viewing-requests',
        summary: 'Створити заявку на перегляд нерухомості',
        description: 'Покупець визначається за токеном Sanctum. Початковий статус заявки — pending.',
        tags: ['ViewingRequests'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['listing_id', 'preferred_date', 'preferred_time'],
                properties: [
                    new OA\Property(
                        property: 'listing_id',
                        type: 'integer',
                        example: 11
                    ),
                    new OA\Property(
                        property: 'preferred_date',
                        description: 'Дата не раніше поточного дня',
                        type: 'string',
                        format: 'date',
                        example: '2026-12-01'
                    ),
                    new OA\Property(
                        property: 'preferred_time',
                        description: 'Час у форматі HH:mm',
                        type: 'string',
                        example: '14:30'
                    ),
                    new OA\Property(
                        property: 'comment',
                        type: 'string',
                        nullable: true,
                        maxLength: 1000,
                        example: 'Бажаю оглянути квартиру'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Заявку успішно створено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ViewingRequest'
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
        $this->authorize('create', ViewingRequest::class);

        $validated = $request->validate([
            'listing_id' => [
                'required',
                'integer',
                'exists:listings,id',
            ],
            'preferred_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'preferred_time' => [
                'required',
                'date_format:H:i',
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $viewingRequest = $request->user()
            ->viewingRequests()
            ->create([
                ...$validated,
                'status' => 'pending',
            ]);

        $viewingRequest->load([
            'listing.property',
            'listing.seller:id,name',
        ]);

        return (new ViewingRequestResource($viewingRequest))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/viewing-requests/{viewingRequest}',
        summary: 'Оновити власну заявку',
        description: 'Редагування дозволено лише власнику заявки зі статусом pending.',
        tags: ['ViewingRequests'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'viewingRequest',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'preferred_date',
                        description: 'Дата не раніше поточного дня',
                        type: 'string',
                        format: 'date',
                        example: '2026-12-02'
                    ),
                    new OA\Property(
                        property: 'preferred_time',
                        description: 'Час у форматі HH:mm',
                        type: 'string',
                        example: '16:00'
                    ),
                    new OA\Property(
                        property: 'comment',
                        type: 'string',
                        nullable: true,
                        maxLength: 1000,
                        example: 'Прошу перенести перегляд'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Заявку успішно оновлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ViewingRequest'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є власником заявки'),
            new OA\Response(response: 404, description: 'Заявку не знайдено'),
            new OA\Response(response: 409, description: 'Редагування заявки з поточним статусом заборонено'),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function update(
        Request $request,
        ViewingRequest $viewingRequest
    ): ViewingRequestResource {
        $this->authorize('update', $viewingRequest);

        abort_unless(
            $viewingRequest->status === 'pending',
            409,
            'Можна редагувати лише заявки зі статусом pending.'
        );

        $validated = $request->validate([
            'preferred_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'preferred_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $viewingRequest->update($validated);

        $viewingRequest->load([
            'listing.property',
            'listing.seller:id,name',
        ]);

        return new ViewingRequestResource($viewingRequest);
    }

    #[OA\Delete(
        path: '/viewing-requests/{viewingRequest}',
        summary: 'Скасувати власну заявку',
        description: 'Змінює статус заявки з pending на cancelled без фізичного видалення запису.',
        tags: ['ViewingRequests'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'viewingRequest',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Заявку успішно скасовано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Заявку успішно скасовано.'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є власником заявки'),
            new OA\Response(response: 404, description: 'Заявку не знайдено'),
            new OA\Response(response: 409, description: 'Скасування заявки з поточним статусом заборонено'),
        ]
    )]
    public function destroy(ViewingRequest $viewingRequest): JsonResponse
    {
        $this->authorize('delete', $viewingRequest);

        abort_unless(
            $viewingRequest->status === 'pending',
            409,
            'Можна скасувати лише заявки зі статусом pending.'
        );

        $viewingRequest->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Заявку успішно скасовано.',
        ], 200);
    }

    #[OA\Get(
        path: '/viewing-requests/incoming',
        summary: 'Отримати вхідні заявки продавця',
        description: 'Повертає заявки на оголошення автентифікованого продавця.',
        tags: ['ViewingRequests'],
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
                description: 'Список вхідних заявок із пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/ViewingRequest'
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
    public function incoming(Request $request): AnonymousResourceCollection
    {
        $this->authorize('incoming', ViewingRequest::class);

        $requests = ViewingRequest::query()
            ->whereHas('listing', function ($query) use ($request) {
                $query->where('seller_id', $request->user()->id);
            })
            ->with(['buyer:id,name', 'listing.property'])
            ->orderByDesc('id')
            ->paginate(10);

        return ViewingRequestResource::collection($requests);
    }

    #[OA\Patch(
        path: '/viewing-requests/{viewingRequest}/decide',
        summary: 'Підтвердити або відхилити заявку',
        description: 'Операція доступна продавцю відповідного оголошення для заявки зі статусом pending. Покупцеві автоматично створюється сповіщення.',
        tags: ['ViewingRequests'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'viewingRequest',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['confirmed', 'rejected'],
                        example: 'confirmed'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Рішення продавця успішно збережено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ViewingRequest'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є продавцем відповідного оголошення'),
            new OA\Response(response: 404, description: 'Заявку не знайдено'),
            new OA\Response(response: 409, description: 'Заявку вже опрацьовано або скасовано'),
            new OA\Response(response: 422, description: 'Недопустиме рішення або помилка валідації'),
        ]
    )]
    public function decide(
        Request $request,
        ViewingRequest $viewingRequest
    ): ViewingRequestResource {
        $this->authorize('decide', $viewingRequest);

        abort_unless(
            $viewingRequest->status === 'pending',
            409,
            'Можна обробляти лише заявки зі статусом pending.'
        );

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,rejected'],
        ]);

        DB::transaction(function () use ($viewingRequest, $validated) {
            $viewingRequest->update([
                'status' => $validated['status'],
            ]);

            $decision = $validated['status'] === 'confirmed'
                ? 'підтверджено'
                : 'відхилено';

            $viewingRequest->buyer->propertyNotifications()->create([
                'message' => "Вашу заявку №{$viewingRequest->id} на перегляд нерухомості {$decision} продавцем.",
                'type' => 'viewing_request',
                'is_read' => false,
            ]);
        });

        $viewingRequest->load([
            'buyer:id,name',
            'listing.property',
        ]);

        return new ViewingRequestResource($viewingRequest);
    }
}
