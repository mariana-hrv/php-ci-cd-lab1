@extends('layouts.app')

@section('title', "Деталі об'єкта нерухомості")

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Об'єкт нерухомості №{{ $property->id }}</h1>

    <a href="{{ route('properties.index') }}"
        class="btn btn-secondary">
        Назад до списку
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header fw-bold">
        Основні характеристики
    </div>

    <div class="card-body">
        <table class="table table-bordered mb-0">
            <tr>
                <th>Тип нерухомості</th>
                <td>{{ $property->property_type }}</td>
            </tr>
            <tr>
                <th>Місто</th>
                <td>{{ $property->city }}</td>
            </tr>
            <tr>
                <th>Адреса</th>
                <td>{{ $property->address }}</td>
            </tr>
            <tr>
                <th>Площа</th>
                <td>{{ $property->area }} м²</td>
            </tr>
            <tr>
                <th>Кількість кімнат</th>
                <td>{{ $property->rooms ?? '—' }}</td>
            </tr>
        </table>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header fw-bold">
        Пов'язані оголошення
    </div>

    <div class="card-body">

        @forelse ($property->listings as $listing)
        <div class="border-bottom pb-3 mb-3">

            <h5>Оголошення №{{ $listing->id }}</h5>

            <p>
                Ціна:
                {{ number_format($listing->price, 2, '.', ' ') }} грн
            </p>

            <p>Статус: {{ $listing->status }}</p>

            <a href="{{ route('listings.show', $listing->id) }}"
                class="btn btn-sm btn-outline-primary">
                Переглянути оголошення
            </a>

        </div>
        @empty
        <p class="text-muted mb-0">
            Пов'язаних оголошень немає.
        </p>
        @endforelse

    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">

        <span class="fw-bold">
            Фотографії нерухомості
        </span>

        <a href="{{ route('properties.photos.index', $property->id) }}"
            class="btn btn-sm btn-outline-primary">
            Переглянути всі фотографії
        </a>

    </div>

    <div class="card-body">

        <div class="row g-3">
            @forelse ($property->photos as $photo)
            <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm overflow-hidden">

                    @if (is_file(public_path('images/properties/' . basename($photo->file_path))))
                    <img src="{{ asset('images/properties/' . basename($photo->file_path)) }}"
                        alt="Фотографія нерухомості"
                        class="card-img-top"
                        style="height: 320px; object-fit: cover;">
                    @else
                    <div class="bg-light text-muted d-flex align-items-center justify-content-center"
                        style="height: 320px;">
                        Фотографія недоступна
                    </div>
                    @endif

                    <div class="card-body d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Фотографія №{{ $photo->id }}
                        </span>

                        @if ($photo->is_primary)
                        <span class="badge bg-success">
                            Основна фотографія
                        </span>
                        @endif
                    </div>

                </div>
            </div>
            @empty
            <p class="text-muted mb-0">
                Фотографій немає.
            </p>
            @endforelse
        </div>
    </div>

    @can('update', $property)

    <div class="card shadow-sm mt-4">

        <div class="card-header fw-bold">
            Керування нерухомістю
        </div>

        <div class="card-body">

            <div class="d-flex gap-2">

                <a href="{{ route('properties.edit', $property->id) }}"
                    class="btn btn-warning">
                    Редагувати нерухомість
                </a>
                @can('delete', $property)

                @if ($property->listings->isEmpty() && $property->photos->isEmpty())

                <form action="{{ route('properties.destroy', $property->id) }}"
                    method="POST"
                    onsubmit="return confirm('Ви дійсно бажаєте видалити об’єкт нерухомості?');">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger">
                        Видалити нерухомість
                    </button>

                </form>

                @endif
                @endcan

            </div>

            @if ($property->listings->isNotEmpty() || $property->photos->isNotEmpty())

            <div class="alert alert-warning mt-3 mb-0">
                Неможливо видалити об'єкт нерухомості,
                оскільки він має пов'язані оголошення
                або фотографії.
            </div>

            @endif

        </div>

    </div>

    @endcan

    @endsection