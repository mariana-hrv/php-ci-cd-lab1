<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ViewingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ViewingRequestController extends Controller
{
    // Отримання заявок поточного покупця
    public function index(Request $request): JsonResponse
    {
        $requests = $request->user()
            ->viewingRequests()
            ->with(['listing.property', 'listing.seller:id,name'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ], 200);
    }

    // Перегляд конкретної заявки
    public function show(Request $request, string $id): JsonResponse
    {
        $viewingRequest = ViewingRequest::query()
            ->with(['listing.property', 'listing.seller:id,name'])
            ->where('buyer_id', $request->user()->id)
            ->findOrFail((int) $id);

        return response()->json([
            'status' => 'success',
            'data' => $viewingRequest,
        ], 200);
    }

    // Створення заявки на перегляд
    public function store(Request $request): JsonResponse
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

        $viewingRequest->load([
            'listing.property',
            'listing.seller:id,name',
        ]);

        return response()->json([
            'status' => 'created',
            'data' => $viewingRequest,
        ], 201);
    }

    // Редагування заявки покупцем
    public function update(Request $request, string $id): JsonResponse
    {
        $viewingRequest = ViewingRequest::query()
            ->where('buyer_id', $request->user()->id)
            ->findOrFail((int) $id);

        if ($viewingRequest->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Можна редагувати лише заявки зі статусом pending.',
            ], 409);
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

        return response()->json([
            'status' => 'success',
            'data' => $viewingRequest->fresh(),
        ], 200);
    }

    // Скасування заявки покупцем
    public function destroy(Request $request, string $id): JsonResponse
    {
        $viewingRequest = ViewingRequest::query()
            ->where('buyer_id', $request->user()->id)
            ->findOrFail((int) $id);

        if ($viewingRequest->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Можна скасувати лише заявки зі статусом pending.',
            ], 409);
        }

        $viewingRequest->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Заявку скасовано.',
        ], 200);
    }

    // Отримання заявок на оголошення поточного продавця
    public function incoming(Request $request): JsonResponse
    {
        $requests = ViewingRequest::query()
            ->whereHas('listing', function ($query) use ($request) {
                $query->where('seller_id', $request->user()->id);
            })
            ->with(['buyer:id,name', 'listing.property'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ], 200);
    }

    // Підтвердження або відхилення заявки продавцем
    public function decide(Request $request, string $id): JsonResponse
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
            return response()->json([
                'status' => 'error',
                'message' => 'Можна обробляти лише заявки зі статусом pending.',
            ], 409);
        }

        $viewingRequest->update([
            'status' => $validated['status'],
        ]);

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
