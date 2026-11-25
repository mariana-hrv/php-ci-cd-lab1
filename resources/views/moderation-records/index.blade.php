@extends('layouts.app')

@section('title', 'Модерація оголошень')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1 class="mb-0">Історія модерації</h1>

    <a href="{{ route('moderation-records.create') }}"
        class="btn btn-success">
        + Модерувати оголошення
    </a>

</div>

<p class="text-muted">
    Перелік рішень адміністраторів щодо оголошень
    про продаж нерухомості.
</p>

<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Оголошення</th>
                <th>Адреса нерухомості</th>
                <th>Адміністратор</th>
                <th>Рішення</th>
                <th>Коментар</th>
                <th>Дата</th>
                <th>Дії</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($records as $record)

            <tr>
                <td>{{ $record->id }}</td>

                <td>
                    <a href="{{ route('listings.show', $record->listing_id) }}">
                        №{{ $record->listing_id }}
                    </a>
                </td>

                <td>
                    {{ $record->listing->property->city ?? '—' }},
                    {{ $record->listing->property->address ?? '—' }}
                </td>

                <td>{{ $record->admin->name ?? '—' }}</td>

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

                <td>{{ $record->comment ?? '—' }}</td>

                <td>
                    {{ $record->created_at?->format('d.m.Y H:i') }}
                </td>

                <td>
                    <a href="{{ route('moderation-records.show', $record->id) }}"
                        class="btn btn-sm btn-info">
                        Перегляд
                    </a>
                </td>

            </tr>

            @empty

            <tr>
                <td colspan="8" class="text-center text-muted">
                    Записів модерації немає.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

{{ $records->links('pagination::bootstrap-5') }}

@endsection