@extends('layouts.app')

@section('title', 'Створення оголошення')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Створення оголошення</h1>

    <a href="{{ route('listings.index') }}"
        class="btn btn-secondary">
        Назад до каталогу
    </a>

</div>

<div class="card shadow-sm">

    <div class="card-header fw-bold">
        Інформація про оголошення
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            Перевірте правильність введених даних.
        </div>
        @endif

        <form action="{{ route('listings.store') }}"
            method="POST" novalidate>

            @csrf

            <div class="mb-3">

                <label for="property_id" class="form-label">
                    Об'єкт нерухомості
                </label>

                <select name="property_id"
                    id="property_id"
                    class="form-select @error('property_id') is-invalid @enderror"
                    required>

                    <option value="">Оберіть нерухомість</option>

                    @foreach ($properties as $property)

                    <option value="{{ $property->id }}"
                        @selected(old('property_id')==$property->id)>

                        №{{ $property->id }} —
                        {{ $property->property_type }},
                        {{ $property->city }},
                        {{ $property->address }}

                    </option>

                    @endforeach

                </select>

                @error('property_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <x-form.input
                name="price"
                label="Ціна"
                type="number"
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
                    class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>

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
                        @selected(old('status', 'draft' )==='draft' )>
                        Чернетка
                    </option>

                    <option value="published"
                        @selected(old('status')==='published' )>
                        Опубліковано
                    </option>

                    <option value="archived"
                        @selected(old('status')==='archived' )>
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
                    value="{{ auth()->user()->name }}"
                    readonly>

            </div>

            <button type="submit" class="btn btn-success">
                Створити оголошення
            </button>

            <a href="{{ route('listings.index') }}"
                class="btn btn-secondary">
                Скасувати
            </a>

        </form>

    </div>
</div>

@endsection