@extends('layouts.app')

@section('title', 'Деталі заявки')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Заявка на перегляд №{{ $viewingRequest->id }}</h1>

    <a href="{{ route('viewing-requests.index') }}"
        class="btn btn-secondary">
        Назад до заявок
    </a>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Інформація про заявку
    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <tr>
                <th>Номер заявки</th>
                <td>{{ $viewingRequest->id }}</td>
            </tr>

            <tr>
                <th>Бажана дата</th>
                <td>{{ $viewingRequest->preferred_date->format('d.m.Y') }}</td>
            </tr>

            <tr>
                <th>Бажаний час</th>
                <td>{{ $viewingRequest->preferred_time }}</td>
            </tr>

            <tr>
                <th>Статус</th>

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
            </tr>

            <tr>
                <th>Коментар покупця</th>
                <td>
                    {{ $viewingRequest->comment ?? 'Коментар відсутній.' }}
                </td>
            </tr>

        </table>

    </div>
</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Інформація про нерухомість та продавця
    </div>

    <div class="card-body">

        <p>
            <strong>Оголошення:</strong>
            №{{ $viewingRequest->listing_id }}
        </p>

        <p>
            <strong>Місто:</strong>
            {{ $viewingRequest->listing->property->city ?? '—' }}
        </p>

        <p>
            <strong>Адреса:</strong>
            {{ $viewingRequest->listing->property->address ?? '—' }}
        </p>

        <p>
            <strong>Продавець:</strong>
            {{ $viewingRequest->listing->seller->name ?? '—' }}
        </p>

        <a href="{{ route('listings.show', $viewingRequest->listing_id) }}"
            class="btn btn-outline-primary">
            Переглянути оголошення
        </a>

    </div>
</div>

@can('update', $viewingRequest)
@if ($viewingRequest->status === 'pending')

<div class="card shadow-sm mt-4">

    <div class="card-header fw-bold">
        Керування заявкою
    </div>

    <div class="card-body d-flex gap-2">

        <a href="{{ route('viewing-requests.edit', $viewingRequest->id) }}"
            class="btn btn-warning">
            Редагувати заявку
        </a>
        @can('delete', $viewingRequest)
        <form action="{{ route('viewing-requests.destroy', $viewingRequest->id) }}"
            method="POST"
            onsubmit="return confirm('Ви дійсно бажаєте скасувати заявку?');">

            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-danger">
                Скасувати заявку
            </button>

        </form>
        @endcan

    </div>

</div>

@endif
@endcan

@endsection