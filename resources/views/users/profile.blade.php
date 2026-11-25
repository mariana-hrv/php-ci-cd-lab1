@extends('layouts.app')

@section('title', 'Особистий профіль')

@section('content')

<h1 class="mb-4">Особистий профіль</h1>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Інформація про користувача
    </div>

    <div class="card-body">

        <table class="table table-bordered mb-0">

            <tr>
                <th>ID користувача</th>
                <td>{{ $user->id }}</td>
            </tr>

            <tr>
                <th>Ім'я</th>
                <td>{{ $user->name }}</td>
            </tr>

            <tr>
                <th>Електронна пошта</th>
                <td>{{ $user->email }}</td>
            </tr>

            <tr>
                <th>Роль</th>
                <td>
                    @if ($user->isAdmin())
                    <span class="badge bg-danger">
                        Адміністратор
                    </span>
                    @else
                    <span class="badge bg-primary">
                        Користувач
                    </span>
                    @endif
                </td>
            </tr>

            <tr>
                <th>Дата реєстрації</th>
                <td>{{ $user->created_at?->format('d.m.Y H:i') }}</td>
            </tr>

        </table>

    </div>
</div>

<h3 class="mb-3">Моя активність</h3>

<div class="row g-3">

    <div class="col-md-3">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
                <h2 class="text-primary">
                    {{ $user->listings_count }}
                </h2>
                <p>Оголошення</p>

                <a href="{{ route('listings.index') }}"
                    class="btn btn-outline-primary btn-sm">
                    Переглянути
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
                <h2 class="text-success">
                    {{ $user->favorite_listings_count }}
                </h2>
                <p>Вибране</p>

                <a href="{{ route('favorites.index') }}"
                    class="btn btn-outline-success btn-sm">
                    Переглянути
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
                <h2 class="text-warning">
                    {{ $user->viewing_requests_count }}
                </h2>
                <p>Заявки на перегляд</p>

                <a href="{{ route('viewing-requests.index') }}"
                    class="btn btn-outline-primary btn-sm">
                    Переглянути
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
                <h2 class="text-info">
                    {{ $user->property_notifications_count }}
                </h2>
                <p>Сповіщення</p>

                <a href="{{ route('notifications.index') }}"
                    class="btn btn-outline-primary btn-sm">
                    Переглянути
                </a>
            </div>
        </div>
    </div>

</div>

<div class="card shadow-sm mt-4">

    <div class="card-header fw-bold">
        Керування обліковим записом
    </div>

    <div class="card-body">

        <a href="{{ route('profile.edit') }}"
            class="btn btn-warning">
            Редагувати профіль
        </a>

    </div>

</div>

@if (! $user->isAdmin())

<div class="card border-danger mt-4">

    <div class="card-header bg-danger text-white">
        Видалення облікового запису
    </div>

    <div class="card-body">

        <p class="text-muted">
            Видалення облікового запису є незворотною дією.
            Для підтвердження введіть поточний пароль.
        </p>

        <form action="{{ route('profile.destroy') }}"
            method="POST"
            onsubmit="return confirm('Ви дійсно бажаєте видалити свій обліковий запис?');">

            @csrf
            @method('DELETE')

            <div class="mb-3">

                <label for="password" class="form-label">
                    Поточний пароль
                </label>

                <input type="password"
                    name="password"
                    id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    autocomplete="current-password"
                    required>

                @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <button type="submit" class="btn btn-danger">
                Видалити обліковий запис
            </button>

        </form>

    </div>

</div>

@endif

@endsection