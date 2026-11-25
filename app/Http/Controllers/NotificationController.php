<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    // Отримання сповіщень поточного користувача
    public function index(Request $request): JsonResponse|View
    {
        $this->authorize('viewAny', Notification::class);
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $notifications,
            ], 200);
        }

        return view('notifications.index', compact('notifications'));
    }

    // Перегляд конкретного сповіщення
    public function show(Request $request, string $id): JsonResponse|View
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail((int) $id);
        $this->authorize('view', $notification);
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $notification,
            ], 200);
        }

        return view('notifications.show', compact('notification'));
    }

    // Позначення сповіщення як прочитаного
    public function markAsRead(
        Request $request,
        string $id
    ): JsonResponse|RedirectResponse {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail((int) $id);
        $this->authorize('markAsRead', $notification);
        $notification->update([
            'is_read' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Сповіщення позначено як прочитане.',
                'data' => $notification->fresh(),
            ], 200);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Сповіщення позначено як прочитане.');
    }

    // Видалення сповіщення
    public function destroy(
        Request $request,
        string $id
    ): JsonResponse|RedirectResponse {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail((int) $id);

        $this->authorize('delete', $notification);
        $notification->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Сповіщення видалено.',
            ], 200);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Сповіщення успішно видалено.');
    }
}
