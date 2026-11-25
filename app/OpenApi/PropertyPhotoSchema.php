<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PropertyPhoto',
    description: 'Фотографія об’єкта нерухомості',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 11
        ),
        new OA\Property(
            property: 'property_id',
            type: 'integer',
            example: 6
        ),
        new OA\Property(
            property: 'file_path',
            type: 'string',
            example: 'properties/photo1.jpg'
        ),
        new OA\Property(
            property: 'is_primary',
            type: 'boolean',
            example: true
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time'
        ),
    ]
)]
class PropertyPhotoSchema {}
