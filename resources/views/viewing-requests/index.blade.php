@extends('layouts.app')

@section('title', 'Мої заявки')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1 class="mb-0">Мої заявки на перегляд</h1>

    <div class="d-flex gap-2">

        <a href="{{ route('viewing-requests.incoming') }}"
            class="btn btn-outline-primary">
            Вхідні заявки
        </a>

        <a href="{{ route('viewing-requests.create') }}"
            class="btn btn-success">
            + Створити заявку
        </a>

    </div>

</div>

<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Оголошення</th>
                <th>Адреса нерухомості</th>
                <th>Продавець</th>
                <th>Бажана дата</th>
                <th>Бажаний час</th>
                <th>Статус</th>
                <th>Дії</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($requests as $viewingRequest)

            <tr>
                <td>{{ $viewingRequest->id }}</td>

                <td>
                    <a href="{{ route('listings.show', $viewingRequest->listing_id) }}">
                        №{{ $viewingRequest->listing_id }}
                    </a>
                </td>

                <td>
                    {{ $viewingRequest->listing->property->city ?? '—' }},
                    {{ $viewingRequest->listing->property->address ?? '—' }}
                </td>

                <td>
                    {{ $viewingRequest->listing->seller->name ?? '—' }}
                </td>

                <td>{{ $viewingRequest->preferred_date->format('d.m.Y') }}</td>

                <td>{{ $viewingRequest->preferred_time }}</td>

                <td>
                    @switch($viewingRequest->status)

                    @case('pending')
                    <span class="badge bg-warning text-dark">
                        Очікує підтвердження
                    </span>
                    @break

                    @case('confirmed')
                    <span class="badge bg-success">
                        Підтверджено
                    </span>
                    @break

                    @case('rejected')
                    <span class="badge bg-danger">
                        Відхилено
                    </span>
                    @break

                    @case('cancelled')
                    <span class="badge bg-secondary">
                        Скасовано
                    </span>
                    @break

                    @default
                    <span class="badge bg-secondary">
                        {{ $viewingRequest->status }}
                    </span>

                    @endswitch
                </td>

                <td>
                    <a href="{{ route('viewing-requests.show', $viewingRequest->id) }}"
                        class="btn btn-sm btn-info">
                        Перегляд
                    </a>
                </td>

            </tr>

            @empty

            <tr>
                <td colspan="8" class="text-center text-muted">
                    Ви ще не подали жодної заявки на перегляд.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

<a href="{{ route('listings.index') }}"
    class="btn btn-primary">
    Перейти до оголошень
</a>

@endsection