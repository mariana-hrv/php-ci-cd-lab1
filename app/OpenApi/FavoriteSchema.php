<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Favorite',
    description: 'Вибране оголошення користувача',
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
            property: 'listing_id',
            type: 'integer',
            example: 11
        ),
        new OA\Property(
            property: 'note',
            type: 'string',
            nullable: true,
            example: 'Цікава квартира'
        ),
        new OA\Property(
            property: 'listing',
            ref: '#/components/schemas/Listing'
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time'
        ),
    ]
)]
class FavoriteSchema {}
