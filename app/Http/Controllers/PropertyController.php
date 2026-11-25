<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    // Отримання всіх об'єктів нерухомості
    public function index(Request $request): JsonResponse|View
    {
        $this->authorize('viewAny', Property::class);
        $query = Property::query()
            ->with(['listings', 'photos']);

        if ($request->expectsJson()) {
            $properties = $query->get();

            return response()->json([
                'status' => 'success',
                'data' => $properties->map(
                    fn (Property $property): array => $this->formatProperty($property)
                )->values()->all(),
            ], 200);
        }

        $properties = $query
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('properties.index', compact('properties'));
    }

    public function create(): View
    {
        $this->authorize('create', Property::class);

        return view('properties.create');
    }

    // Створення нового об'єкта нерухомості
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Property::class);
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

        if ($request->routeIs('properties.store-form')) {
            return redirect()
                ->route('properties.index')
                ->with('success', 'Об’єкт нерухомості успішно створено!');
        }

        $property->load(['listings', 'photos']);

        return response()->json([
            'status' => 'created',
            'data' => $this->formatProperty($property),
        ], 201);
    }

    // Отримання об'єкта нерухомості за ID
    public function show(Request $request, string $id): JsonResponse|View
    {
        $property = Property::query()
            ->with(['listings', 'photos'])
            ->find((int) $id);

        if ($property === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Об’єкт нерухомості з таким ID не знайдено.',
                ], 404);
            }

            abort(404);
        }
        $this->authorize('view', $property);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $this->formatProperty($property),
            ], 200);
        }

        return view('properties.show', compact('property'));
    }

    public function edit(Request $request, string $id): View
    {
        $property = Property::findOrFail((int) $id);

        $this->authorize('update', $property);

        return view('properties.edit', compact('property'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $property = Property::findOrFail((int) $id);

        $this->authorize('update', $property);

        $validated = $request->validate([
            'propertyType' => ['required', 'string', 'min:2', 'max:100'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'area' => ['required', 'numeric', 'gt:0'],
            'rooms' => ['required', 'integer', 'min:1'],
        ]);

        $property->update([
            'property_type' => $validated['propertyType'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'area' => $validated['area'],
            'rooms' => $validated['rooms'],
        ]);

        return redirect()
            ->route('properties.index')
            ->with('success', 'Об’єкт нерухомості успішно оновлено!');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $property = Property::findOrFail((int) $id);

        $this->authorize('delete', $property);

        abort_if(
            $property->listings()->exists()
                || $property->photos()->exists(),
            409,
            'Неможливо видалити об’єкт із пов’язаними записами.'
        );

        $property->delete();

        return redirect()
            ->route('properties.index')
            ->with('success', 'Об’єкт нерухомості успішно видалено!');
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
