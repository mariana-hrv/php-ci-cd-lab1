<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // Отримання всіх оголошень
    public function index(): JsonResponse
    {
        $listings = Listing::query()
            ->with(['property', 'seller:id,name'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $listings,
        ], 200);
    }

    // Отримання оголошення за ID
    public function show(string $id): JsonResponse
    {
        $listing = Listing::query()
            ->with(['property', 'seller:id,name'])
            ->findOrFail((int) $id);

        return response()->json([
            'status' => 'success',
            'data' => $listing,
        ], 200);
    }

    // Створення оголошення
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'price' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $listing = Listing::create([
            ...$validated,
            'seller_id' => $request->user()->id,
        ]);

        $listing->load(['property', 'seller:id,name']);

        return response()->json([
            'status' => 'created',
            'data' => $listing,
        ], 201);
    }

    // Оновлення оголошення
    public function update(Request $request, string $id): JsonResponse
    {
        $listing = Listing::findOrFail((int) $id);

        if ($listing->seller_id !== $request->user()->id) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Редагувати оголошення може лише його продавець.',
            ], 403);
        }

        $validated = $request->validate([
            'price' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $listing->update($validated);
        $listing->load(['property', 'seller:id,name']);

        return response()->json([
            'status' => 'success',
            'data' => $listing,
        ], 200);
    }

    // Видалення оголошення
    public function destroy(Request $request, string $id): JsonResponse
    {
        $listing = Listing::findOrFail((int) $id);

        if ($listing->seller_id !== $request->user()->id) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Видалити оголошення може лише його продавець.',
            ], 403);
        }

        $listing->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Оголошення видалено.',
        ], 200);
    }
}
