<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Отримання сповіщень поточного користувача
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ], 200);
    }

    // Перегляд конкретного сповіщення
    public function show(Request $request, string $id): JsonResponse
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail((int) $id);

        return response()->json([
            'status' => 'success',
            'data' => $notification,
        ], 200);
    }

    // Позначення сповіщення як прочитаного
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail((int) $id);

        $notification->update([
            'is_read' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Сповіщення позначено як прочитане.',
            'data' => $notification->fresh(),
        ], 200);
    }

    // Видалення сповіщення
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail((int) $id);

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Сповіщення видалено.',
        ], 200);
    }
}
