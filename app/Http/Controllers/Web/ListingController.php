<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Listing;
use App\Models\Property;
use App\Services\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct(
        private readonly ListingService $listingService
    ) {}

    // Отримання всіх оголошень
    public function index(Request $request): JsonResponse|View
    {
        $this->authorize('viewAny', Listing::class);
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
        $this->authorize('view', $listing);
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
        $this->authorize('create', Listing::class);
        $properties = Property::query()
            ->orderBy('city')
            ->orderBy('address')
            ->get();

        return view('listings.create', compact('properties'));
    }

    // Створення оголошення
    public function store(StoreListingRequest $request): JsonResponse|RedirectResponse
    {
        $listing = $this->listingService->create(
            $request->validated(),
            $request->user()->id
        );

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

        $this->authorize('update', $listing);

        return view('listings.edit', compact('listing'));
    }

    // Оновлення оголошення
    public function update(UpdateListingRequest $request, string $id): JsonResponse|RedirectResponse
    {
        $listing = Listing::findOrFail((int) $id);

        $listing = $this->listingService->update(
            $listing,
            $request->validated()
        );

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

        $this->authorize('delete', $listing);

        $this->listingService->delete($listing);

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
