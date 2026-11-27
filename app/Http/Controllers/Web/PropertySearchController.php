<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertySearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Property::class);
        // Валідація параметрів пошуку
        $filters = $request->validate([
            'city' => ['nullable', 'string', 'max:100'],
            'propertyType' => ['nullable', 'string', 'max:100'],
            'rooms' => ['nullable', 'integer', 'min:1'],
        ]);

        // Формування запиту до бази данихє
        $query = Property::query()
            ->with([
                'listings' => fn ($query) => $query->visibleTo(request()->user()),
                'photos',
            ]);

        // Фільтрація за містом
        if (! empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // Фільтрація за типом нерухомості
        if (! empty($filters['propertyType'])) {
            $query->where(
                'property_type',
                $filters['propertyType']
            );
        }

        // Фільтрація за кількістю кімнат
        if (isset($filters['rooms'])) {
            $query->where('rooms', $filters['rooms']);
        }

        // Отримання результатів із SQLite
        $properties = $query->orderBy('id')->get();

        // Формування JSON-відповіді
        $results = $properties->map(function (Property $property) {
            return [
                'id' => $property->id,
                'propertyType' => $property->property_type,
                'city' => $property->city,
                'address' => $property->address,
                'area' => (float) $property->area,
                'rooms' => $property->rooms,
                'listings' => $property->listings,
                'photos' => $property->photos,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $results->values()->all(),
        ], 200);
    }
}
