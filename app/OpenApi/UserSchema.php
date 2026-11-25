<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    description: 'Користувач системи продажу нерухомості',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 9
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'Тестовий покупець'
        ),
        new OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            example: 'buyer@example.com'
        ),
        new OA\Property(
            property: 'role',
            type: 'string',
            example: 'user'
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time'
        ),
    ]
)]
class UserSchema {}
