<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyPhotoController extends Controller
{
    // Перегляд фотографій об'єкта нерухомості
    public function index(string $propertyId): JsonResponse
    {
        $property = Property::findOrFail((int) $propertyId);

        $photos = $property->photos()
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $photos,
        ], 200);
    }

    // Додавання фотографії до нерухомості
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => [
                'required',
                'integer',
                'exists:properties,id',
            ],
            'file_path' => [
                'required',
                'string',
                'max:255',
            ],
            'is_primary' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $property = Property::findOrFail(
            $validated['property_id']
        );

        // Перевірка, чи користувач продає цей об'єкт
        $isSeller = $property->listings()
            ->where('seller_id', $request->user()->id)
            ->exists();

        if (! $isSeller) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Додавати фотографії може лише продавець об’єкта.',
            ], 403);
        }

        $photo = $property->photos()->create([
            'file_path' => $validated['file_path'],
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return response()->json([
            'status' => 'created',
            'data' => $photo,
        ], 201);
    }

    // Видалення фотографії
    public function destroy(Request $request, string $id): JsonResponse
    {
        $photo = PropertyPhoto::with('property')
            ->findOrFail((int) $id);

        $isSeller = $photo->property->listings()
            ->where('seller_id', $request->user()->id)
            ->exists();

        if (! $isSeller) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Видаляти фотографії може лише продавець об’єкта.',
            ], 403);
        }

        $photo->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Фотографію видалено.',
        ], 200);
    }
}
