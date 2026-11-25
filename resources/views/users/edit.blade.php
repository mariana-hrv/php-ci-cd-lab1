@extends('layouts.app')

@section('title', 'Редагування профілю')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Редагування особистого профілю</h1>

    <a href="{{ route('profile') }}"
        class="btn btn-secondary">
        Назад до профілю
    </a>

</div>

<div class="card shadow-sm">

    <div class="card-header fw-bold">
        Особисті дані користувача
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            Перевірте правильність введених даних.
        </div>
        @endif

        <form action="{{ route('profile.update') }}"
            method="POST" novalidate>

            @csrf
            @method('PUT')

            <x-form.input
                name="name"
                label="Ім'я користувача"
                :value="$user->name"
                required />

            <x-form.input
                name="email"
                label="Електронна пошта"
                type="email"
                :value="$user->email"
                required />

            <div class="mb-3">

                <label class="form-label">Роль користувача</label>

                <input type="text"
                    class="form-control"
                    value="{{ $user->isAdmin() ? 'Адміністратор' : 'Користувач' }}"
                    readonly>

            </div>

            <button type="submit" class="btn btn-success">
                Зберегти зміни
            </button>

            <a href="{{ route('profile') }}"
                class="btn btn-secondary">
                Скасувати
            </a>

        </form>

    </div>
</div>

@endsection