@extends('layouts.app')

@section('title', 'Вхідні заявки')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Вхідні заявки на перегляд</h1>

    <a href="{{ route('viewing-requests.index') }}"
        class="btn btn-secondary">
        Мої заявки
    </a>

</div>

<p class="text-muted">
    Заявки покупців на перегляд нерухомості,
    оголошення про яку ви розмістили.
</p>

<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Покупець</th>
                <th>Оголошення</th>
                <th>Адреса нерухомості</th>
                <th>Бажана дата</th>
                <th>Бажаний час</th>
                <th>Коментар</th>
                <th>Статус</th>
                <th>Дії</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($requests as $viewingRequest)

            <tr>
                <td>{{ $viewingRequest->id }}</td>

                <td>
                    {{ $viewingRequest->buyer->name ?? '—' }}
                </td>

                <td>
                    <a href="{{ route('listings.show', $viewingRequest->listing_id) }}">
                        №{{ $viewingRequest->listing_id }}
                    </a>
                </td>

                <td>
                    {{ $viewingRequest->listing->property->city ?? '—' }},
                    {{ $viewingRequest->listing->property->address ?? '—' }}
                </td>

                <td>{{ $viewingRequest->preferred_date->format('d.m.Y') }}</td>

                <td>{{ $viewingRequest->preferred_time }}</td>

                <td>{{ $viewingRequest->comment ?? '—' }}</td>

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
                    @if ($viewingRequest->status === 'pending')

                    <div class="d-flex gap-2">

                        <form action="{{ route('viewing-requests.decide', $viewingRequest->id) }}"
                            method="POST">

                            @csrf
                            @method('PATCH')

                            <input type="hidden"
                                name="status"
                                value="confirmed">

                            <button type="submit" class="btn btn-sm btn-success">
                                Підтвердити
                            </button>

                        </form>

                        <form action="{{ route('viewing-requests.decide', $viewingRequest->id) }}"
                            method="POST"
                            onsubmit="return confirm('Відхилити заявку?');">

                            @csrf
                            @method('PATCH')

                            <input type="hidden"
                                name="status"
                                value="rejected">

                            <button type="submit" class="btn btn-sm btn-danger">
                                Відхилити
                            </button>

                        </form>

                    </div>

                    @else

                    <span class="text-muted">Оброблено</span>

                    @endif
                </td>
            </tr>

            @empty

            <tr>
                <td colspan="8" class="text-center text-muted">
                    Вхідних заявок на перегляд немає.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection