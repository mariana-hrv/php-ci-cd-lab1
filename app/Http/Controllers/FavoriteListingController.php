<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteListingController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $this->authorize('viewAny', Favorite::class);
        /** @var User $user */
        $user = $request->user();

        $listings = $user->favoriteListings()
            ->with('property')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $listings,
            ], 200);
        }

        $allListings = Listing::query()
            ->with('property')
            ->orderBy('id')
            ->get();

        $selectedIds = $listings->pluck('id')->toArray();

        return view('favorites.index', compact(
            'listings',
            'allListings',
            'selectedIds'
        ));
    }

    public function sync(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('sync', Favorite::class);
        if (! $request->expectsJson() && ! $request->has('listing_ids')) {
            $request->merge(['listing_ids' => []]);
        }

        $validated = $request->validate([
            'listing_ids' => ['present', 'array'],
            'listing_ids.*' => [
                'integer',
                'distinct',
                'exists:listings,id',
            ],
        ]);

        /** @var User $user */
        $user = $request->user();

        $user->favoriteListings()->sync(
            $validated['listing_ids']
        );

        if ($request->expectsJson()) {
            $listings = $user->favoriteListings()
                ->with('property')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Список вибраного оновлено.',
                'data' => $listings,
            ], 200);
        }

        return redirect()->route('favorites.index')
            ->with('success', 'Список вибраного успішно оновлено.');
    }
}
