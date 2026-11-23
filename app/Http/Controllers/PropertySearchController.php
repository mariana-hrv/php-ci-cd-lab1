<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertySearchController extends Controller
{
    private array $properties = [
        [
            'id' => 1,
            'propertyType' => 'Квартира',
            'city' => 'Івано-Франківськ',
            'address' => 'вул. Незалежності, 10',
            'area' => 65.5,
            'rooms' => 2,
        ],
        [
            'id' => 2,
            'propertyType' => 'Будинок',
            'city' => 'Львів',
            'address' => 'вул. Шевченка, 25',
            'area' => 120.0,
            'rooms' => 4,
        ],
    ];

    public function search(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'city' => ['nullable', 'string', 'max:100'],
            'propertyType' => ['nullable', 'string', 'max:100'],
            'rooms' => ['nullable', 'integer', 'min:1'],
        ]);

        $city = $filters['city'] ?? null;
        $propertyType = $filters['propertyType'] ?? null;
        $rooms = isset($filters['rooms'])
            ? (int) $filters['rooms']
            : null;

        $results = array_filter(
            $this->properties,
            function (array $property) use ($city, $propertyType, $rooms): bool {
                return ($city === null || $property['city'] === $city)
                    && ($propertyType === null || $property['propertyType'] === $propertyType)
                    && ($rooms === null || $property['rooms'] === $rooms);
            }
        );

        return response()->json([
            'status' => 'success',
            'data' => array_values($results),
        ], 200);
    }
}
