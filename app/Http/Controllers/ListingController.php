<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ListingController extends Controller
{
    // Отримання всіх оголошень
    public function index(Request $request): JsonResponse|View
    {
        $query = Listing::query()
            ->with(['property', 'seller:id,name']);

        if ($request->expectsJson()) {
            $listings = $query->orderBy('id')->get();

            return response()->json([
                'status' => 'success',
                'data' => $listings,
            ], 200);
        }

        $listings = $query
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('listings.index', compact('listings'));
    }

    // Отримання оголошення за ID
    public function show(Request $request, string $id): JsonResponse|View
    {
        $listing = Listing::query()
            ->with(['property', 'seller:id,name'])
            ->findOrFail((int) $id);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $listing,
            ], 200);
        }

        return view('listings.show', compact('listing'));
    }

    public function create(): View
    {
        $properties = Property::query()
            ->orderBy('city')
            ->orderBy('address')
            ->get();

        return view('listings.create', compact('properties'));
    }

    // Створення оголошення
    public function store(Request $request): JsonResponse|RedirectResponse
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

        if (! $request->expectsJson()) {
            return redirect()
                ->route('listings.index')
                ->with('success', 'Оголошення успішно створено!');
        }

        $listing->load(['property', 'seller:id,name']);

        return response()->json([
            'status' => 'created',
            'data' => $listing,
        ], 201);
    }

    public function edit(Request $request, string $id): View
    {
        $listing = Listing::query()
            ->with(['property', 'seller:id,name'])
            ->findOrFail((int) $id);

        abort_unless(
            $listing->seller_id === $request->user()->id,
            403
        );

        return view('listings.edit', compact('listing'));
    }

    // Оновлення оголошення
    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $listing = Listing::findOrFail((int) $id);

        if ($listing->seller_id !== $request->user()->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'access_denied',
                    'message' => 'Редагувати оголошення може лише його продавець.',
                ], 403);
            }

            abort(403);
        }

        $validated = $request->validate([
            'price' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $listing->update($validated);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('listings.index')
                ->with('success', 'Оголошення успішно оновлено!');
        }

        $listing->load(['property', 'seller:id,name']);

        return response()->json([
            'status' => 'success',
            'data' => $listing,
        ], 200);
    }

    // Видалення оголошення
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $listing = Listing::findOrFail((int) $id);

        if ($listing->seller_id !== $request->user()->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'access_denied',
                    'message' => 'Видалити оголошення може лише його продавець.',
                ], 403);
            }

            abort(403);
        }

        $listing->delete();

        if (! $request->expectsJson()) {
            return redirect()
                ->route('listings.index')
                ->with('success', 'Оголошення успішно видалено!');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Оголошення видалено.',
        ], 200);
    }
}
