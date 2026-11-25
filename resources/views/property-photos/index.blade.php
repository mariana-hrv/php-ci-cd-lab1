@extends('layouts.app')

@section('title', 'Фотографії нерухомості')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Фотографії об'єкта №{{ $property->id }}</h1>

    <a href="{{ route('properties.show', $property->id) }}"
        class="btn btn-secondary">
        Назад до об'єкта
    </a>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-body">

        <p>
            <strong>Тип нерухомості:</strong>
            {{ $property->property_type }}
        </p>

        <p class="mb-0">
            <strong>Адреса:</strong>
            {{ $property->city }}, {{ $property->address }}
        </p>

    </div>
</div>

<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Шлях до фотографії</th>
                <th>Тип фотографії</th>
                <th>Дата додавання</th>
                @if ($canManagePhotos)
                <th>Дії</th>
                @endif
            </tr>
        </thead>

        <tbody>

            @forelse ($photos as $photo)

            <tr>
                <td>{{ $photo->id }}</td>

                <td>{{ $photo->file_path }}</td>

                <td>
                    @if ($photo->is_primary)

                    <span class="badge bg-success">
                        Основна
                    </span>

                    @else

                    <span class="badge bg-secondary">
                        Додаткова
                    </span>

                    @endif
                </td>

                <td>
                    {{ $photo->created_at?->format('d.m.Y H:i') }}
                </td>

                @if ($canManagePhotos)

                <td>
                    <form action="{{ route('property-photos.destroy', $photo->id) }}"
                        method="POST"
                        onsubmit="return confirm('Ви дійсно бажаєте видалити фотографію?');">

                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-sm btn-danger">
                            Видалити
                        </button>

                    </form>
                </td>

                @endif
            </tr>

            @empty

            <tr>
                <td colspan="{{ $canManagePhotos ? 5 : 4 }}"
                    class="text-center text-muted">
                    Фотографій поки немає.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

@if ($canManagePhotos)

<div class="card shadow-sm mt-4">

    <div class="card-header fw-bold">
        Додавання фотографії
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            Перевірте правильність введених даних.
        </div>
        @endif

        <form action="{{ route('property-photos.store') }}"
            method="POST" novalidate>

            @csrf

            <input type="hidden"
                name="property_id"
                value="{{ $property->id }}">

            <x-form.input
                name="file_path"
                label="Шлях до фотографії"
                placeholder="properties/apartment-6.jpg"
                required />

            <div class="form-check mb-3">

                <input type="hidden"
                    name="is_primary"
                    value="0">

                <input type="checkbox"
                    class="form-check-input"
                    name="is_primary"
                    id="is_primary"
                    value="1"
                    @checked(old('is_primary', false))>

                <label class="form-check-label" for="is_primary">
                    Основна фотографія
                </label>

            </div>

            <button type="submit" class="btn btn-success">
                Додати фотографію
            </button>

        </form>

    </div>
</div>

@endif

@endsection