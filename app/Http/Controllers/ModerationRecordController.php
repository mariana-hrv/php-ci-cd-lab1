<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ModerationRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ModerationRecordController extends Controller
{
    // Перегляд усіх записів модерації
    public function index(Request $request): JsonResponse|View
    {
        $this->authorize('viewAny', ModerationRecord::class);

        $query = ModerationRecord::query()
            ->with(['listing.property', 'admin:id,name'])
            ->orderByDesc('id');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $query->get(),
            ], 200);
        }

        $records = $query->paginate(10);

        return view('moderation-records.index', compact('records'));
    }

    // Перегляд окремого запису модерації
    public function show(Request $request, string $id): JsonResponse|View
    {
        $this->authorize('viewAny', ModerationRecord::class);

        $record = ModerationRecord::query()
            ->with(['listing.property', 'admin:id,name'])
            ->findOrFail((int) $id);

        $this->authorize('view', $record);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $record,
            ], 200);
        }

        return view('moderation-records.show', compact('record'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ModerationRecord::class);

        $listings = Listing::query()
            ->with(['property', 'seller:id,name'])
            ->where('status', 'draft')
            ->orderByDesc('id')
            ->get();

        return view('moderation-records.create', compact('listings'));
    }

    // Схвалення або відхилення оголошення
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', ModerationRecord::class);

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

        if (! $request->expectsJson()) {
            return redirect()
                ->route('moderation-records.index')
                ->with('success', 'Рішення модерації успішно збережено!');
        }

        return response()->json([
            'status' => 'created',
            'message' => 'Рішення модерації збережено.',
            'data' => $record,
        ], 201);
    }
}
