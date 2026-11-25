@extends('layouts.app')

@section('title', 'Деталі оголошення')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Оголошення №{{ $listing->id }}</h1>

    <a href="{{ route('listings.index') }}"
        class="btn btn-secondary">
        Назад до оголошень
    </a>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Інформація про оголошення
    </div>

    <div class="card-body">

        <h2 class="text-success mb-3">
            {{ number_format($listing->price, 2, '.', ' ') }} грн
        </h2>

        <p>
            <strong>Статус:</strong>
            {{ $listing->status }}
        </p>

        <p>
            <strong>Продавець:</strong>
            {{ $listing->seller->name ?? '—' }}
        </p>

        <p>
            <strong>Дата створення:</strong>
            {{ $listing->created_at?->format('d.m.Y H:i') }}
        </p>

        <h5>Опис оголошення</h5>

        <p>{{ $listing->description ?? 'Опис відсутній.' }}</p>

    </div>
</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Характеристики нерухомості
    </div>

    <div class="card-body">

        @if ($listing->property)

        <table class="table table-bordered">

            <tr>
                <th>Тип нерухомості</th>
                <td>{{ $listing->property->property_type }}</td>
            </tr>

            <tr>
                <th>Місто</th>
                <td>{{ $listing->property->city }}</td>
            </tr>

            <tr>
                <th>Адреса</th>
                <td>{{ $listing->property->address }}</td>
            </tr>

            <tr>
                <th>Площа</th>
                <td>{{ $listing->property->area }} м²</td>
            </tr>

            <tr>
                <th>Кількість кімнат</th>
                <td>{{ $listing->property->rooms ?? '—' }}</td>
            </tr>

        </table>

        <a href="{{ route('properties.show', $listing->property->id) }}"
            class="btn btn-outline-primary">
            Переглянути об'єкт нерухомості
        </a>

        @else

        <p class="text-muted">
            Інформація про нерухомість відсутня.
        </p>

        @endif

    </div>
</div>

@auth

@if ($listing->seller_id === auth()->id())

<div class="card shadow-sm mt-4">

    <div class="card-header fw-bold">
        Керування оголошенням
    </div>

    <div class="card-body d-flex gap-2">

        <a href="{{ route('listings.edit', $listing->id) }}"
            class="btn btn-warning">
            Редагувати оголошення
        </a>

        <form action="{{ route('listings.destroy', $listing->id) }}"
            method="POST"
            onsubmit="return confirm('Ви дійсно бажаєте видалити оголошення?');">

            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-danger">
                Видалити оголошення
            </button>

        </form>

    </div>

</div>

@endif

@endauth

@endsection