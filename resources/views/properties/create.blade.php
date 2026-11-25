@extends('layouts.app')

@section('title', 'Додавання нерухомості')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Додавання об'єкта нерухомості</h1>

    <a href="{{ route('properties.index') }}"
        class="btn btn-secondary">
        Назад до каталогу
    </a>

</div>

<div class="card shadow-sm">

    <div class="card-header fw-bold">
        Характеристики нерухомості
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            Перевірте правильність введених даних.
        </div>
        @endif

        <form action="{{ route('properties.store-form') }}"
            method="POST" novalidate>

            @csrf

            <x-form.input
                name="propertyType"
                label="Тип нерухомості"
                placeholder="Наприклад, apartment"
                required />

            <x-form.input
                name="city"
                label="Місто"
                placeholder="Івано-Франківськ"
                required />

            <x-form.input
                name="address"
                label="Адреса"
                placeholder="Вулиця, номер будинку"
                required />

            <x-form.input
                name="area"
                label="Площа, м²"
                type="number"
                step="0.01"
                min="0.01"
                required />

            <x-form.input
                name="rooms"
                label="Кількість кімнат"
                type="number"
                min="1"
                step="1"
                required />

            <button type="submit" class="btn btn-success">
                Зберегти нерухомість
            </button>

            <a href="{{ route('properties.index') }}"
                class="btn btn-secondary">
                Скасувати
            </a>

        </form>

    </div>
</div>

@endsection