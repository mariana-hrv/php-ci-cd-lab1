<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function profile(Request $request): View
    {
        $user = $request->user();

        $user->loadCount([
            'listings',
            'favoriteListings',
            'viewingRequests',
            'propertyNotifications',
        ]);

        return view('users.profile', compact('user'));
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $users = User::query()
            ->withCount([
                'listings',
                'favoriteListings',
                'viewingRequests',
            ])
            ->orderBy('id')
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function show(Request $request, string $id): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $user = User::query()
            ->withCount([
                'listings',
                'favoriteListings',
                'viewingRequests',
                'propertyNotifications',
            ])
            ->findOrFail((int) $id);

        return view('users.show', compact('user'));
    }

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('users.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return redirect()
            ->route('profile')
            ->with('success', 'Особистий профіль успішно оновлено!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user->isAdmin(), 403);

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        abort_if(
            $user->listings()->exists()
                || $user->viewingRequests()->exists()
                || $user->moderationRecords()->exists()
                || $user->favoriteListings()->exists()
                || $user->propertyNotifications()->exists(),
            409,
            'Неможливо видалити обліковий запис із пов’язаними даними.'
        );

        $user->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Обліковий запис успішно видалено!');
    }
}
