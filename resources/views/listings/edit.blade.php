@extends('layouts.app')

@section('title', 'Редагування оголошення')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Редагування оголошення №{{ $listing->id }}</h1>

    <a href="{{ route('listings.show', $listing->id) }}"
        class="btn btn-secondary">
        Назад до оголошення
    </a>

</div>

<div class="card shadow-sm">

    <div class="card-header fw-bold">
        Редагування інформації про оголошення
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            Перевірте правильність введених даних.
        </div>
        @endif

        <form action="{{ route('listings.update', $listing->id) }}"
            method="POST" novalidate>

            @csrf
            @method('PUT')

            <div class="mb-3">

                <label class="form-label">
                    Об'єкт нерухомості
                </label>

                <input type="text"
                    class="form-control"
                    value="{{ $listing->property->city }}, {{ $listing->property->address }}"
                    readonly>

            </div>

            <x-form.input
                name="price"
                label="Ціна, грн"
                type="number"
                :value="$listing->price"
                step="0.01"
                min="0.01"
                required />

            <div class="mb-3">

                <label for="description" class="form-label">
                    Опис оголошення
                </label>

                <textarea name="description"
                    id="description"
                    rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $listing->description) }}</textarea>

                @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <div class="mb-3">

                <label for="status" class="form-label">
                    Статус оголошення
                </label>

                <select name="status"
                    id="status"
                    class="form-select @error('status') is-invalid @enderror">

                    <option value="draft"
                        @selected(old('status', $listing->status) === 'draft')>
                        Чернетка
                    </option>

                    <option value="published"
                        @selected(old('status', $listing->status) === 'published')>
                        Опубліковано
                    </option>

                    <option value="archived"
                        @selected(old('status', $listing->status) === 'archived')>
                        Архівовано
                    </option>

                </select>

                @error('status')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <div class="mb-3">

                <label class="form-label">Продавець</label>

                <input type="text"
                    class="form-control"
                    value="{{ $listing->seller->name }}"
                    readonly>

            </div>

            <button type="submit" class="btn btn-success">
                Зберегти зміни
            </button>

            <a href="{{ route('listings.show', $listing->id) }}"
                class="btn btn-secondary">
                Скасувати
            </a>

        </form>

    </div>
</div>

@endsection