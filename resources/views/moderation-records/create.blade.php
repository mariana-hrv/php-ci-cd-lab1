@extends('layouts.app')

@section('title', 'Модерація оголошення')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Модерація оголошення</h1>

    <a href="{{ route('moderation-records.index') }}"
        class="btn btn-secondary">
        Назад до історії модерації
    </a>

</div>

<div class="card shadow-sm">

    <div class="card-header fw-bold">
        Рішення адміністратора
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            Перевірте правильність введених даних.
        </div>
        @endif

        <form action="{{ route('moderation-records.store') }}"
            method="POST" novalidate>

            @csrf

            <div class="mb-3">

                <label for="listing_id" class="form-label">
                    Оголошення для модерації
                </label>

                <select name="listing_id"
                    id="listing_id"
                    class="form-select @error('listing_id') is-invalid @enderror"
                    required>

                    <option value="">Оберіть оголошення</option>

                    @foreach ($listings as $listing)

                    <option value="{{ $listing->id }}"
                        @selected(old('listing_id')==$listing->id)>

                        №{{ $listing->id }} —
                        {{ $listing->property->city }},
                        {{ $listing->property->address }} —
                        {{ $listing->seller->name }}

                    </option>

                    @endforeach

                </select>

                @error('listing_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <div class="mb-3">

                <label for="decision" class="form-label">
                    Рішення адміністратора
                </label>

                <select name="decision"
                    id="decision"
                    class="form-select @error('decision') is-invalid @enderror"
                    required>

                    <option value="">Оберіть рішення</option>

                    <option value="approved"
                        @selected(old('decision')==='approved' )>
                        Схвалити оголошення
                    </option>

                    <option value="rejected"
                        @selected(old('decision')==='rejected' )>
                        Відхилити оголошення
                    </option>

                </select>

                @error('decision')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <div class="mb-3">

                <label for="comment" class="form-label">
                    Коментар адміністратора
                </label>

                <textarea name="comment"
                    id="comment"
                    rows="4"
                    maxlength="1000"
                    class="form-control @error('comment') is-invalid @enderror">{{ old('comment') }}</textarea>

                @error('comment')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Адміністратор
                </label>

                <input type="text"
                    class="form-control"
                    value="{{ auth()->user()->name }}"
                    readonly>

            </div>

            <button type="submit" class="btn btn-success">
                Зберегти рішення
            </button>

            <a href="{{ route('moderation-records.index') }}"
                class="btn btn-secondary">
                Скасувати
            </a>

        </form>

    </div>
</div>

@endsection