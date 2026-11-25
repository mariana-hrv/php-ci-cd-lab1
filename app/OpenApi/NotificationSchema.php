<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Notification',
    description: 'Сповіщення користувача',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'user_id',
            type: 'integer',
            example: 9
        ),
        new OA\Property(
            property: 'type',
            type: 'string',
            example: 'viewing_request'
        ),
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Вашу заявку на перегляд нерухомості підтверджено.'
        ),
        new OA\Property(
            property: 'is_read',
            type: 'boolean',
            example: false
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time'
        ),
    ]
)]
class NotificationSchema {}
