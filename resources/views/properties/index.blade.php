@extends('layouts.app')

@section('title', "Об'єкти нерухомості")

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1 class="mb-0">Об'єкти нерухомості</h1>

    @can('create', \App\Models\Property::class)
    <a href="{{ route('properties.create') }}"
        class="btn btn-success">
        + Додати нерухомість
    </a>
    @endcan

</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Тип нерухомості</th>
                <th>Місто</th>
                <th>Адреса</th>
                <th>Площа, м²</th>
                <th>Кімнати</th>
                <th>Оголошення</th>
                <th>Фотографії</th>
                <th>Дії</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($properties as $property)
            <tr>
                <td>{{ $property->id }}</td>
                <td>{{ $property->property_type }}</td>
                <td>{{ $property->city }}</td>
                <td>{{ $property->address }}</td>
                <td>{{ $property->area }}</td>
                <td>{{ $property->rooms ?? '—' }}</td>

                <td>
                    <span class="badge bg-primary">
                        {{ $property->listings->count() }}
                    </span>
                </td>

                <td>
                    <span class="badge bg-secondary">
                        {{ $property->photos->count() }}
                    </span>
                </td>

                <td>
                    <a href="{{ route('properties.show', $property->id) }}"
                        class="btn btn-sm btn-info">
                        Перегляд
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted">
                    Об'єктів нерухомості не знайдено.
                </td>
            </tr>
            @endforelse
        </tbody>

    </table>
</div>

{{ $properties->links('pagination::bootstrap-5') }}

@endsection