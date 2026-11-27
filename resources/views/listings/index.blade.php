@extends('layouts.app')

@section('title', 'Оголошення про продаж')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Оголошення нерухомості</h1>

    @auth
    <a href="{{ route('listings.create') }}" class="btn btn-success">
        + Створити оголошення
    </a>
    @endauth
</div>

<div class="row g-4 mb-4">

    @forelse ($listings as $listing)

    @php
    $photo = $listing->property?->photos->firstWhere('is_primary', true)
    ?? $listing->property?->photos->first();

    $photoPath = $photo
    ? 'images/properties/' . basename($photo->file_path)
    : null;

    $type = match ($listing->property?->property_type) {
    'apartment' => 'Квартира',
    'house' => 'Будинок',
    'land' => 'Земельна ділянка',
    default => $listing->property?->property_type ?? 'Нерухомість',
    };

    [$statusText, $statusColor] = match ($listing->status) {
    'published' => ['Опубліковано', 'success'],
    'draft' => ['Чернетка', 'secondary'],
    'sold' => ['Продано', 'primary'],
    'archived' => ['Архівовано', 'dark'],
    'rejected' => ['Відхилено', 'danger'],
    default => [$listing->status, 'warning'],
    };
    @endphp

    <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100 shadow-sm overflow-hidden">

            @if ($photoPath && is_file(public_path($photoPath)))
            <img src="{{ asset($photoPath) }}"
                class="card-img-top"
                alt="{{ $type }}"
                style="height: 230px; object-fit: cover;">
            @else
            <div class="bg-light text-muted d-flex align-items-center justify-content-center"
                style="height: 230px;">
                Фотографія недоступна
            </div>
            @endif

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">
                        №{{ $listing->id }}
                    </small>

                    <span class="badge bg-{{ $statusColor }}">
                        {{ $statusText }}
                    </span>
                </div>

                <h4 class="fw-bold text-primary mb-2">
                    {{ number_format($listing->price, 2, '.', ' ') }} грн
                </h4>

                <h5 class="card-title">{{ $type }}</h5>

                <p class="mb-1">
                    {{ $listing->property->city ?? '—' }}
                </p>

                <p class="text-muted mb-3">
                    {{ $listing->property->address ?? '—' }}
                </p>

                <div class="d-flex gap-3 mb-3">
                    <span>
                        {{ $listing->property->area ?? '—' }} м²
                    </span>

                    <span>
                        Кімнат: {{ $listing->property->rooms ?? '—' }}
                    </span>
                </div>

                <small class="text-muted">
                    Продавець: {{ $listing->seller->name ?? '—' }}
                </small>

            </div>

            <div class="card-footer bg-white border-top-0">

                <a href="{{ route('listings.show', $listing->id) }}"
                    class="btn btn-outline-primary w-100 mb-2">
                    Переглянути оголошення
                </a>

                <div class="d-flex flex-wrap gap-2">

                    @can('update', $listing)
                    <a href="{{ route('listings.edit', $listing->id) }}"
                        class="btn btn-sm btn-outline-warning">
                        Редагувати
                    </a>
                    @endcan

                    @can('delete', $listing)
                    <form action="{{ route('listings.destroy', $listing->id) }}"
                        method="POST"
                        onsubmit="return confirm('Ви дійсно бажаєте видалити оголошення?');">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="btn btn-sm btn-outline-danger">
                            Видалити
                        </button>
                    </form>
                    @endcan

                </div>

            </div>
        </div>
    </div>

    @empty
    <div class="col-12">
        <div class="alert alert-light text-center">
            Оголошень не знайдено.
        </div>
    </div>
    @endforelse

</div>

{{ $listings->links('pagination::bootstrap-5') }}

@endsection