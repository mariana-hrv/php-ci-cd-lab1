@extends('layouts.app')

@section('title', 'Користувачі системи')

@section('content')

<h1 class="mb-4">Користувачі системи</h1>

<p class="text-muted">
    Перелік зареєстрованих користувачів та інформація
    про їхню активність у системі.
</p>

<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Ім'я</th>
                <th>Електронна пошта</th>
                <th>Роль</th>
                <th>Оголошення</th>
                <th>Вибране</th>
                <th>Заявки</th>
                <th>Дата реєстрації</th>
                <th>Дії</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($users as $user)

            <tr>
                <td>{{ $user->id }}</td>

                <td>{{ $user->name }}</td>

                <td>{{ $user->email }}</td>

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

                <td>{{ $user->listings_count }}</td>

                <td>{{ $user->favorite_listings_count }}</td>

                <td>{{ $user->viewing_requests_count }}</td>

                <td>
                    {{ $user->created_at?->format('d.m.Y H:i') }}
                </td>

                <td>
                    <a href="{{ route('users.show', $user->id) }}"
                        class="btn btn-sm btn-info">
                        Перегляд
                    </a>
                </td>

            </tr>

            @empty

            <tr>
                <td colspan="9" class="text-center text-muted">
                    Користувачів не знайдено.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

{{ $users->links('pagination::bootstrap-5') }}

@endsection