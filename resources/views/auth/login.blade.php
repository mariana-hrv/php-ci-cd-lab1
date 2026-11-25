@extends('layouts.app')

@section('title', 'Вхід до системи')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

        <div class="card shadow-sm">

            <div class="card-header bg-white">
                <h2 class="mb-0">Вхід до системи</h2>
            </div>

            <div class="card-body">

                @if ($errors->any())
                <div class="alert alert-danger">
                    Перевірте правильність введених даних.
                </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST">

                    @csrf

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
                            autocomplete="current-password"
                            required>

                        @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            id="remember"
                            name="remember">

                        <label class="form-check-label" for="remember">
                            Запам'ятати мене
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        Увійти
                    </button>

                </form>

                <p class="text-center mt-3 mb-0">
                    Ще не маєте облікового запису?
                    <a href="{{ route('register') }}">
                        Зареєструватися
                    </a>
                </p>

            </div>
        </div>
    </div>
</div>

@endsection