<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Listing',
    description: 'Оголошення про продаж нерухомості',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 11
        ),
        new OA\Property(
            property: 'price',
            type: 'number',
            format: 'double',
            example: 2500000
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            nullable: true,
            example: 'Продаж двокімнатної квартири'
        ),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['draft', 'published', 'archived'],
            example: 'published'
        ),
        new OA\Property(
            property: 'seller_id',
            type: 'integer',
            example: 9
        ),
        new OA\Property(
            property: 'property',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'property_type', type: 'string'),
                new OA\Property(property: 'city', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
                new OA\Property(property: 'area', type: 'number'),
                new OA\Property(property: 'rooms', type: 'integer'),
            ]
        ),
        new OA\Property(
            property: 'seller',
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
class ListingSchema {}
