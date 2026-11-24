<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ModerationRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModerationRecordController extends Controller
{
    // Перегляд усіх записів модерації
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Доступ дозволено лише адміністратору.',
            ], 403);
        }

        $records = ModerationRecord::query()
            ->with(['listing.property', 'admin:id,name'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $records,
        ], 200);
    }

    // Перегляд окремого запису модерації
    public function show(Request $request, string $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Доступ дозволено лише адміністратору.',
            ], 403);
        }

        $record = ModerationRecord::query()
            ->with(['listing.property', 'admin:id,name'])
            ->findOrFail((int) $id);

        return response()->json([
            'status' => 'success',
            'data' => $record,
        ], 200);
    }

    // Схвалення або відхилення оголошення
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Модерувати оголошення може лише адміністратор.',
            ], 403);
        }

        $validated = $request->validate([
            'listing_id' => [
                'required',
                'integer',
                'exists:listings,id',
            ],
            'decision' => [
                'required',
                'in:approved,rejected',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $listing = Listing::findOrFail(
            $validated['listing_id']
        );

        // Запис рішення та зміна статусу в одній транзакції
        $record = DB::transaction(function () use (
            $listing,
            $validated,
            $request
        ) {
            $record = $listing->moderationRecords()->create([
                'admin_id' => $request->user()->id,
                'decision' => $validated['decision'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $newStatus = $validated['decision'] === 'approved'
                ? 'published'
                : 'draft';

            $listing->update([
                'status' => $newStatus,
            ]);

            return $record->load([
                'listing.property',
                'admin:id,name',
            ]);
        });

        return response()->json([
            'status' => 'created',
            'message' => 'Рішення модерації збережено.',
            'data' => $record,
        ], 201);
    }
}
