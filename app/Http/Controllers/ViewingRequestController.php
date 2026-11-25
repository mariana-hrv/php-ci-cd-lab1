<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ViewingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ViewingRequestController extends Controller
{
    // Отримання заявок поточного покупця
    public function index(Request $request): JsonResponse|View
    {
        $requests = $request->user()
            ->viewingRequests()
            ->with(['listing.property', 'listing.seller:id,name'])
            ->orderByDesc('id')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $requests,
            ], 200);
        }

        return view('viewing-requests.index', compact('requests'));
    }

    // Перегляд конкретної заявки
    public function show(Request $request, string $id): JsonResponse|View
    {
        $viewingRequest = ViewingRequest::query()
            ->with(['listing.property', 'listing.seller:id,name'])
            ->where('buyer_id', $request->user()->id)
            ->findOrFail((int) $id);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $viewingRequest,
            ], 200);
        }

        return view(
            'viewing-requests.show',
            compact('viewingRequest')
        );
    }

    public function create(Request $request): View
    {
        $listings = Listing::query()
            ->with(['property', 'seller:id,name'])
            ->where('status', 'published')
            ->where('seller_id', '!=', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return view('viewing-requests.create', compact('listings'));
    }

    // Створення заявки на перегляд
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'listing_id' => [
                'required',
                'integer',
                'exists:listings,id',
            ],
            'preferred_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'preferred_time' => [
                'required',
                'date_format:H:i',
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $viewingRequest = $request->user()
            ->viewingRequests()
            ->create([
                ...$validated,
                'status' => 'pending',
            ]);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('viewing-requests.index')
                ->with('success', 'Заявку на перегляд успішно створено!');
        }

        $viewingRequest->load([
            'listing.property',
            'listing.seller:id,name',
        ]);

        return response()->json([
            'status' => 'created',
            'data' => $viewingRequest,
        ], 201);
    }

    public function edit(Request $request, string $id): View
    {
        $viewingRequest = ViewingRequest::query()
            ->with(['listing.property', 'listing.seller:id,name'])
            ->where('buyer_id', $request->user()->id)
            ->findOrFail((int) $id);

        abort_unless($viewingRequest->status === 'pending', 409);

        return view('viewing-requests.edit', compact('viewingRequest'));
    }

    // Редагування заявки покупцем
    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $viewingRequest = ViewingRequest::query()
            ->where('buyer_id', $request->user()->id)
            ->findOrFail((int) $id);

        if ($viewingRequest->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Можна редагувати лише заявки зі статусом pending.',
                ], 409);
            }

            abort(409);
        }

        $validated = $request->validate([
            'preferred_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'preferred_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $viewingRequest->update($validated);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('viewing-requests.index')
                ->with('success', 'Заявку успішно оновлено!');
        }

        return response()->json([
            'status' => 'success',
            'data' => $viewingRequest->fresh(),
        ], 200);
    }

    // Скасування заявки покупцем
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $viewingRequest = ViewingRequest::query()
            ->where('buyer_id', $request->user()->id)
            ->findOrFail((int) $id);

        if ($viewingRequest->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Можна скасувати лише заявки зі статусом pending.',
                ], 409);
            }

            abort(409);
        }

        $viewingRequest->update([
            'status' => 'cancelled',
        ]);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('viewing-requests.index')
                ->with('success', 'Заявку успішно скасовано!');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Заявку скасовано.',
        ], 200);
    }

    // Отримання заявок на оголошення поточного продавця
    public function incoming(Request $request): JsonResponse|View
    {
        $requests = ViewingRequest::query()
            ->whereHas('listing', function ($query) use ($request) {
                $query->where('seller_id', $request->user()->id);
            })
            ->with(['buyer:id,name', 'listing.property'])
            ->orderByDesc('id')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $requests,
            ], 200);
        }

        return view('viewing-requests.incoming', compact('requests'));
    }

    // Підтвердження або відхилення заявки продавцем
    public function decide(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,rejected'],
        ]);

        $viewingRequest = ViewingRequest::query()
            ->whereHas('listing', function ($query) use ($request) {
                $query->where('seller_id', $request->user()->id);
            })
            ->findOrFail((int) $id);

        if ($viewingRequest->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Можна обробляти лише заявки зі статусом pending.',
                ], 409);
            }

            abort(409);
        }

        DB::transaction(function () use ($viewingRequest, $validated) {

            $viewingRequest->update([
                'status' => $validated['status'],
            ]);

            $decision = $validated['status'] === 'confirmed'
                ? 'підтверджено'
                : 'відхилено';

            $viewingRequest->buyer->propertyNotifications()->create([
                'message' => "Вашу заявку №{$viewingRequest->id} на перегляд нерухомості {$decision} продавцем.",
                'type' => 'viewing_request',
                'is_read' => false,
            ]);
        });

        if (! $request->expectsJson()) {
            return redirect()
                ->route('viewing-requests.incoming')
                ->with('success', 'Рішення щодо заявки успішно збережено!');
        }

        $viewingRequest->load([
            'buyer:id,name',
            'listing.property',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Статус заявки оновлено.',
            'data' => $viewingRequest,
        ], 200);
    }
}
