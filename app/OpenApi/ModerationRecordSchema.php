<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ModerationRecord',
    description: 'Запис модерації оголошення',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'listing_id',
            type: 'integer',
            example: 11
        ),
        new OA\Property(
            property: 'admin_id',
            type: 'integer',
            example: 1
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
            example: 'Оголошення відповідає вимогам.'
        ),
        new OA\Property(
            property: 'listing',
            ref: '#/components/schemas/Listing'
        ),
        new OA\Property(
            property: 'admin',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
            ]
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time'
        ),
    ]
)]
class ModerationRecordSchema {}
