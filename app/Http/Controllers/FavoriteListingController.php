<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteListingController extends Controller
{
    // Отримання вибраних оголошень користувача
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $listings = $user->favoriteListings()
            ->with('property')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $listings,
        ], 200);
    }

    // Оновлення списку вибраних оголошень
    public function sync(Request $request): JsonResponse
    {
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

        // Синхронізація зв'язку багато-до-багатьох
        $user->favoriteListings()->sync(
            $validated['listing_ids']
        );

        // Завантаження оновленого списку
        $listings = $user->favoriteListings()
            ->with('property')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Список вибраного оновлено.',
            'data' => $listings,
        ], 200);
    }
}
