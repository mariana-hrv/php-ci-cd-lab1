@extends('layouts.app')

@section('title', 'Деталі користувача')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Користувач №{{ $user->id }}</h1>

    <a href="{{ route('users.index') }}"
        class="btn btn-secondary">
        Назад до користувачів
    </a>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Інформація про обліковий запис
    </div>

    <div class="card-body">

        <table class="table table-bordered mb-0">

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

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Активність користувача
    </div>

    <div class="card-body">

        <table class="table table-bordered mb-0">

            <tr>
                <th>Кількість оголошень</th>
                <td>{{ $user->listings_count }}</td>
            </tr>

            <tr>
                <th>Кількість вибраних оголошень</th>
                <td>{{ $user->favorite_listings_count }}</td>
            </tr>

            <tr>
                <th>Кількість заявок на перегляд</th>
                <td>{{ $user->viewing_requests_count }}</td>
            </tr>

            <tr>
                <th>Кількість сповіщень</th>
                <td>{{ $user->property_notifications_count }}</td>
            </tr>

        </table>

    </div>
</div>

@endsection