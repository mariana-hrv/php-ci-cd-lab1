@extends('layouts.app')

@section('title', 'Головна сторінка')

@section('content')

<div class="p-5 mb-4 bg-white rounded-3 shadow-sm">

    <h1 class="display-5 fw-bold">
        Електронна система продажу нерухомості
    </h1>

    <p class="col-md-10 fs-5 text-secondary mt-3">
        Вебплатформа для пошуку, перегляду та розміщення
        оголошень про продаж нерухомості.
    </p>

    <a href="{{ route('listings.index') }}"
        class="btn btn-primary btn-lg">
        Перейти до оголошень
    </a>

    <a href="{{ route('properties.index') }}"
        class="btn btn-outline-secondary btn-lg">
        Об'єкти нерухомості
    </a>

</div>

<div class="row g-4">

    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h4>Пошук нерухомості</h4>
                <p class="text-secondary">
                    Переглядайте доступні об'єкти та їх характеристики.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h4>Оголошення</h4>
                <p class="text-secondary">
                    Створюйте та керуйте оголошеннями про продаж.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h4>Заявки на перегляд</h4>
                <p class="text-secondary">
                    Подавайте заявки на перегляд обраної нерухомості.
                </p>
            </div>
        </div>
    </div>

</div>

@endsection