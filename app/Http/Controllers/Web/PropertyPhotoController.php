<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyPhotoController extends Controller
{
    // Перегляд фотографій об'єкта нерухомості
    public function index(Request $request, string $propertyId): JsonResponse|View
    {
        $this->authorize('viewAny', PropertyPhoto::class);
        $property = Property::findOrFail((int) $propertyId);

        $photos = $property->photos()
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $photos,
            ], 200);
        }

        $canManagePhotos = $request->user()?->can(
            'create',
            [PropertyPhoto::class, $property]
        ) ?? false;

        return view('property-photos.index', compact(
            'property',
            'photos',
            'canManagePhotos'
        ));
    }

    // Додавання фотографії до нерухомості
    public function store(Request $request): JsonResponse|RedirectResponse
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

        $this->authorize('create', [PropertyPhoto::class, $property]);

        $photo = $property->photos()->create([
            'file_path' => $validated['file_path'],
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('properties.photos.index', $property->id)
                ->with('success', 'Фотографію успішно додано!');
        }

        return response()->json([
            'status' => 'created',
            'data' => $photo,
        ], 201);
    }

    // Видалення фотографії
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $photo = PropertyPhoto::with('property')
            ->findOrFail((int) $id);

        $this->authorize('delete', $photo);

        $propertyId = $photo->property_id;

        $photo->delete();

        if (! $request->expectsJson()) {
            return redirect()
                ->route('properties.photos.index', $propertyId)
                ->with('success', 'Фотографію успішно видалено!');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Фотографію видалено.',
        ], 200);
    }
}
