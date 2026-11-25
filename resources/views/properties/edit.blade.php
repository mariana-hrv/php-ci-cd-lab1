@extends('layouts.app')

@section('title', 'Редагування нерухомості')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Редагування об'єкта №{{ $property->id }}</h1>

    <a href="{{ route('properties.show', $property->id) }}"
        class="btn btn-secondary">
        Назад до об'єкта
    </a>

</div>

<div class="card shadow-sm">

    <div class="card-header fw-bold">
        Редагування характеристик нерухомості
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            Перевірте правильність введених даних.
        </div>
        @endif

        <form action="{{ route('properties.update', $property->id) }}"
            method="POST" novalidate>

            @csrf
            @method('PUT')

            <x-form.input
                name="propertyType"
                label="Тип нерухомості"
                :value="$property->property_type"
                required />

            <x-form.input
                name="city"
                label="Місто"
                :value="$property->city"
                required />

            <x-form.input
                name="address"
                label="Адреса"
                :value="$property->address"
                required />

            <x-form.input
                name="area"
                label="Площа, м²"
                type="number"
                :value="$property->area"
                step="0.01"
                min="0.01"
                required />

            <x-form.input
                name="rooms"
                label="Кількість кімнат"
                type="number"
                :value="$property->rooms"
                min="1"
                step="1"
                required />

            <button type="submit" class="btn btn-success">
                Зберегти зміни
            </button>

            <a href="{{ route('properties.show', $property->id) }}"
                class="btn btn-secondary">
                Скасувати
            </a>

        </form>

    </div>
</div>

@endsection