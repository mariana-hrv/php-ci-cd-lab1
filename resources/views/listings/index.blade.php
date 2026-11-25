@extends('layouts.app')

@section('title', 'Оголошення про продаж')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1 class="mb-0">Оголошення нерухомості</h1>

    @auth
    <a href="{{ route('listings.create') }}"
        class="btn btn-success">
        + Створити оголошення
    </a>
    @endauth

</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Тип нерухомості</th>
                <th>Місто</th>
                <th>Адреса</th>
                <th>Продавець</th>
                <th>Ціна, грн</th>
                <th>Статус</th>
                <th>Дії</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($listings as $listing)
            <tr>
                <td>{{ $listing->id }}</td>

                <td>{{ $listing->property->property_type ?? '—' }}</td>

                <td>{{ $listing->property->city ?? '—' }}</td>

                <td>{{ $listing->property->address ?? '—' }}</td>

                <td>{{ $listing->seller->name ?? '—' }}</td>

                <td>
                    {{ number_format($listing->price, 2, '.', ' ') }}
                </td>

                <td>
                    @switch($listing->status)
                    @case('published')
                    <span class="badge bg-success">
                        Опубліковано
                    </span>
                    @break

                    @case('draft')
                    <span class="badge bg-secondary">
                        Чернетка
                    </span>
                    @break

                    @case('sold')
                    <span class="badge bg-primary">
                        Продано
                    </span>
                    @break

                    @case('archived')
                    <span class="badge bg-dark">
                        Архівовано
                    </span>
                    @break

                    @case('rejected')
                    <span class="badge bg-danger">
                        Відхилено
                    </span>
                    @break

                    @default
                    <span class="badge bg-warning text-dark">
                        {{ $listing->status }}
                    </span>
                    @endswitch
                </td>

                <td>
                    <a href="{{ route('listings.show', $listing->id) }}"
                        class="btn btn-sm btn-info">
                        Перегляд
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">
                    Оголошень не знайдено.
                </td>
            </tr>
            @endforelse
        </tbody>

    </table>
</div>

{{ $listings->links('pagination::bootstrap-5') }}

@endsection