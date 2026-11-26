<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class NotificationApiController extends Controller
{
    #[OA\Get(
        path: '/notifications',
        summary: 'Отримати власні сповіщення',
        description: 'Повертає лише сповіщення автентифікованого користувача.',
        tags: ['Notifications'],
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
                description: 'Список сповіщень із пагінацією',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/Notification'
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
        $this->authorize('viewAny', Notification::class);

        $notifications = $request->user()
            ->propertyNotifications()
            ->orderByDesc('id')
            ->paginate(10);

        return NotificationResource::collection($notifications);
    }

    #[OA\Get(
        path: '/notifications/{notification}',
        summary: 'Переглянути окреме сповіщення',
        tags: ['Notifications'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'notification',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Дані сповіщення',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Notification'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є власником сповіщення'),
            new OA\Response(response: 404, description: 'Сповіщення не знайдено'),
        ]
    )]
    public function show(
        Notification $notification
    ): NotificationResource {
        $this->authorize('view', $notification);

        return new NotificationResource($notification);
    }

    #[OA\Patch(
        path: '/notifications/{notification}/read',
        summary: 'Позначити сповіщення прочитаним',
        description: 'Встановлює значення is_read у true. Операція доступна лише власнику сповіщення.',
        tags: ['Notifications'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'notification',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Сповіщення позначено прочитаним',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Notification'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є власником сповіщення'),
            new OA\Response(response: 404, description: 'Сповіщення не знайдено'),
        ]
    )]
    public function markAsRead(
        Notification $notification
    ): NotificationResource {
        $this->authorize('markAsRead', $notification);

        $notification->update([
            'is_read' => true,
        ]);

        return new NotificationResource($notification);
    }

    #[OA\Delete(
        path: '/notifications/{notification}',
        summary: 'Видалити власне сповіщення',
        tags: ['Notifications'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'notification',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Сповіщення успішно видалено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Сповіщення успішно видалено.'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не автентифіковано'),
            new OA\Response(response: 403, description: 'Користувач не є власником сповіщення'),
            new OA\Response(response: 404, description: 'Сповіщення не знайдено'),
        ]
    )]
    public function destroy(
        Notification $notification
    ): JsonResponse {
        $this->authorize('delete', $notification);

        $notification->delete();

        return response()->json([
            'message' => 'Сповіщення успішно видалено.',
        ], 200);
    }
}
