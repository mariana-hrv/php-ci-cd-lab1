@extends('layouts.app')

@section('title', 'Деталі модерації')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Запис модерації №{{ $record->id }}</h1>

    <a href="{{ route('moderation-records.index') }}"
        class="btn btn-secondary">
        Назад до історії модерації
    </a>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Інформація про рішення адміністратора
    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <tr>
                <th>Адміністратор</th>
                <td>{{ $record->admin->name ?? '—' }}</td>
            </tr>

            <tr>
                <th>Дата модерації</th>
                <td>
                    {{ $record->created_at?->format('d.m.Y H:i') }}
                </td>
            </tr>

            <tr>
                <th>Рішення</th>

                <td>
                    @if ($record->decision === 'approved')

                    <span class="badge bg-success">
                        Схвалено
                    </span>

                    @elseif ($record->decision === 'rejected')

                    <span class="badge bg-danger">
                        Відхилено
                    </span>

                    @else

                    {{ $record->decision }}

                    @endif
                </td>
            </tr>

            <tr>
                <th>Коментар адміністратора</th>
                <td>
                    {{ $record->comment ?? 'Коментар відсутній.' }}
                </td>
            </tr>

        </table>

    </div>
</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Інформація про оголошення
    </div>

    <div class="card-body">

        <p>
            <strong>Оголошення:</strong>
            №{{ $record->listing_id }}
        </p>

        <p>
            <strong>Місто:</strong>
            {{ $record->listing->property->city ?? '—' }}
        </p>

        <p>
            <strong>Адреса:</strong>
            {{ $record->listing->property->address ?? '—' }}
        </p>

        <p>
            <strong>Поточний статус оголошення:</strong>
            {{ $record->listing->status ?? '—' }}
        </p>

        <a href="{{ route('listings.show', $record->listing_id) }}"
            class="btn btn-outline-primary">
            Переглянути оголошення
        </a>

    </div>
</div>

@endsection