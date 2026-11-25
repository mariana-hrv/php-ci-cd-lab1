<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Property',
    description: 'Об’єкт нерухомості',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 6
        ),
        new OA\Property(
            property: 'property_type',
            type: 'string',
            example: 'Квартира'
        ),
        new OA\Property(
            property: 'city',
            type: 'string',
            example: 'Івано-Франківськ'
        ),
        new OA\Property(
            property: 'address',
            type: 'string',
            example: 'вул. Галицька, 15'
        ),
        new OA\Property(
            property: 'area',
            type: 'number',
            format: 'double',
            example: 75.5
        ),
        new OA\Property(
            property: 'rooms',
            type: 'integer',
            example: 3
        ),
        new OA\Property(
            property: 'listings',
            type: 'array',
            items: new OA\Items(
                ref: '#/components/schemas/Listing'
            )
        ),
        new OA\Property(
            property: 'photos',
            type: 'array',
            items: new OA\Items(
                ref: '#/components/schemas/PropertyPhoto'
            )
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time'
        ),
    ]
)]
class PropertySchema {}
