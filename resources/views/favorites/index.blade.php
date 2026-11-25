@extends('layouts.app')

@section('title', 'Вибрані оголошення')

@section('content')

<h1 class="mb-4">Вибрані оголошення</h1>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Мій список вибраного
    </div>

    <div class="card-body">

        @forelse ($listings as $listing)

        <div class="border-bottom pb-3 mb-3">

            <h5>
                Оголошення №{{ $listing->id }}
            </h5>

            <p>
                {{ $listing->property->city ?? '—' }},
                {{ $listing->property->address ?? '—' }}
            </p>

            <p class="text-success fw-bold">
                {{ number_format($listing->price, 2, '.', ' ') }} грн
            </p>

            <a href="{{ route('listings.show', $listing->id) }}"
                class="btn btn-sm btn-outline-primary">
                Переглянути оголошення
            </a>

        </div>

        @empty

        <p class="text-muted mb-0">
            Ви ще не додали жодного оголошення до вибраного.
        </p>

        @endforelse

    </div>
</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Керування списком вибраного
    </div>

    <div class="card-body">

        <p class="text-muted">
            Позначте оголошення, які бажаєте зберегти.
        </p>

        @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        @can('sync', \App\Models\Favorite::class)
        <form action="{{ route('favorites.sync') }}" method="POST">

            @csrf
            @method('PUT')

            @forelse ($allListings as $listing)

            <div class="form-check border-bottom py-3">

                <input
                    class="form-check-input"
                    type="checkbox"
                    name="listing_ids[]"
                    id="listing_{{ $listing->id }}"
                    value="{{ $listing->id }}"
                    @checked(in_array(
                    $listing->id,
                (array) old('listing_ids', $selectedIds)
                ))
                >

                <label class="form-check-label"
                    for="listing_{{ $listing->id }}">

                    <strong>
                        Оголошення №{{ $listing->id }}
                    </strong>

                    —
                    {{ $listing->property->city ?? '—' }},
                    {{ $listing->property->address ?? '—' }}

                    —
                    {{ number_format($listing->price, 2, '.', ' ') }} грн

                </label>

            </div>

            @empty

            <p>Доступних оголошень немає.</p>

            @endforelse

            <button type="submit" class="btn btn-success mt-3">
                Зберегти вибране
            </button>

            <a href="{{ route('listings.index') }}"
                class="btn btn-secondary mt-3">
                До оголошень
            </a>

        </form>
        @endcan

    </div>
</div>

@endsection