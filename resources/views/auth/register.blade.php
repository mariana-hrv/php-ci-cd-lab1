@extends('layouts.app')

@section('title', 'Реєстрація')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

        <div class="card shadow-sm">

            <div class="card-header bg-white">
                <h2 class="mb-0">Реєстрація</h2>
            </div>

            <div class="card-body">

                @if ($errors->any())
                <div class="alert alert-danger">
                    Виправте помилки у формі.
                </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST">

                    @csrf

                    <x-form.input
                        name="name"
                        label="Ім'я"
                        autocomplete="name"
                        required />

                    <x-form.input
                        name="email"
                        label="Електронна пошта"
                        type="email"
                        autocomplete="email"
                        required />

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Пароль
                        </label>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            required>

                        @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="form-text">
                            Щонайменше 8 символів.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">
                            Підтвердження пароля
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                            required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        Зареєструватися
                    </button>

                </form>

                <p class="text-center mt-3 mb-0">
                    Вже маєте обліковий запис?
                    <a href="{{ route('login') }}">
                        Увійти
                    </a>
                </p>

            </div>
        </div>
    </div>
</div>

@endsection