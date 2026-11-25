@extends('layouts.app')

@section('title', 'Редагування заявки')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Редагування заявки №{{ $viewingRequest->id }}</h1>

    <a href="{{ route('viewing-requests.show', $viewingRequest->id) }}"
        class="btn btn-secondary">
        Назад до заявки
    </a>

</div>

<div class="card shadow-sm">

    <div class="card-header fw-bold">
        Редагування інформації про заявку
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            Перевірте правильність введених даних.
        </div>
        @endif

        <form action="{{ route('viewing-requests.update', $viewingRequest->id) }}"
            method="POST" novalidate>

            @csrf
            @method('PUT')

            <div class="mb-3">

                <label class="form-label">Оголошення</label>

                <input type="text"
                    class="form-control"
                    value="№{{ $viewingRequest->listing_id }} — {{ $viewingRequest->listing->property->city }}, {{ $viewingRequest->listing->property->address }}"
                    readonly>

            </div>

            <x-form.input
                name="preferred_date"
                label="Бажана дата перегляду"
                type="date"
                :value="$viewingRequest->preferred_date->format('Y-m-d')"
                :min="now()->toDateString()"
                required />

            <x-form.input
                name="preferred_time"
                label="Бажаний час перегляду"
                type="time"
                :value="substr($viewingRequest->preferred_time, 0, 5)"
                required />

            <div class="mb-3">

                <label for="comment" class="form-label">
                    Коментар покупця
                </label>

                <textarea name="comment"
                    id="comment"
                    rows="4"
                    maxlength="1000"
                    class="form-control @error('comment') is-invalid @enderror">{{ old('comment', $viewingRequest->comment) }}</textarea>

                @error('comment')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <div class="mb-3">

                <label class="form-label">Статус заявки</label>

                <input type="text"
                    class="form-control"
                    value="Очікує підтвердження"
                    readonly>

            </div>

            <button type="submit" class="btn btn-success">
                Зберегти зміни
            </button>

            <a href="{{ route('viewing-requests.show', $viewingRequest->id) }}"
                class="btn btn-secondary">
                Скасувати
            </a>

        </form>

    </div>
</div>

@endsection