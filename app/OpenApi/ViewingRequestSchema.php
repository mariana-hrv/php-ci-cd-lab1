<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ViewingRequest',
    description: 'Заявка на перегляд нерухомості',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'buyer_id',
            type: 'integer',
            example: 9
        ),
        new OA\Property(
            property: 'listing_id',
            type: 'integer',
            example: 11
        ),
        new OA\Property(
            property: 'preferred_date',
            type: 'string',
            format: 'date',
            example: '2026-12-01'
        ),
        new OA\Property(
            property: 'preferred_time',
            type: 'string',
            example: '14:30'
        ),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['pending', 'confirmed', 'rejected', 'cancelled'],
            example: 'pending'
        ),
        new OA\Property(
            property: 'comment',
            type: 'string',
            nullable: true,
            example: 'Бажаю оглянути квартиру'
        ),
        new OA\Property(
            property: 'listing',
            ref: '#/components/schemas/Listing'
        ),
        new OA\Property(
            property: 'buyer',
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
class ViewingRequestSchema {}
