<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    // Отримання всіх об'єктів нерухомості
    public function index(): JsonResponse
    {
        $properties = Property::query()
            ->with(['listings', 'photos'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $properties->map(
                fn (Property $property): array => $this->formatProperty($property)
            )->values()->all(),
        ], 200);
    }

    // Створення нового об'єкта нерухомості
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'propertyType' => ['required', 'string', 'min:2', 'max:100'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'area' => ['required', 'numeric', 'gt:0'],
            'rooms' => ['required', 'integer', 'min:1'],
        ]);

        $property = Property::create([
            'property_type' => $validated['propertyType'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'area' => $validated['area'],
            'rooms' => $validated['rooms'],
        ]);

        $property->load(['listings', 'photos']);

        return response()->json([
            'status' => 'created',
            'data' => $this->formatProperty($property),
        ], 201);
    }

    // Отримання об'єкта нерухомості за ID
    public function show(string $id): JsonResponse
    {
        $property = Property::query()
            ->with(['listings', 'photos'])
            ->find((int) $id);

        if ($property === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Об’єкт нерухомості з таким ID не знайдено.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->formatProperty($property),
        ], 200);
    }

    // Формування відповіді у форматі попереднього API
    private function formatProperty(Property $property): array
    {
        return [
            'id' => $property->id,
            'propertyType' => $property->property_type,
            'city' => $property->city,
            'address' => $property->address,
            'area' => (float) $property->area,
            'rooms' => $property->rooms === null
                ? null
                : (int) $property->rooms,
            'listings' => $property->listings,
            'photos' => $property->photos,
        ];
    }
}
