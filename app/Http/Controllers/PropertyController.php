<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
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

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->properties,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'propertyType' => ['required', 'string', 'min:2', 'max:100'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'area' => ['required', 'numeric', 'gt:0'],
            'rooms' => ['required', 'integer', 'min:1'],
        ]);

        $newProperty = [
            'id' => max(array_column($this->properties, 'id')) + 1,
            'propertyType' => $validated['propertyType'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'area' => (float) $validated['area'],
            'rooms' => (int) $validated['rooms'],
        ];

        $this->properties[] = $newProperty;

        return response()->json([
            'status' => 'created',
            'data' => $newProperty,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $propertyId = (int) $id;

        foreach ($this->properties as $property) {
            if ($property['id'] === $propertyId) {
                return response()->json([
                    'status' => 'success',
                    'data' => $property,
                ], 200);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Обʼєкт нерухомості з таким ID не знайдено.',
        ], 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
