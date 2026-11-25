@extends('layouts.app')

@section('title', 'Деталі сповіщення')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>Сповіщення №{{ $notification->id }}</h1>

    <a href="{{ route('notifications.index') }}"
        class="btn btn-secondary">
        Назад до сповіщень
    </a>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-header fw-bold">
        Інформація про сповіщення
    </div>

    <div class="card-body">

        <p>
            <strong>Дата створення:</strong>
            {{ $notification->created_at?->format('d.m.Y H:i') }}
        </p>

        <p>
            <strong>Статус:</strong>

            @if ($notification->is_read)
            <span class="badge bg-success">
                Прочитано
            </span>
            @else
            <span class="badge bg-warning text-dark">
                Не прочитано
            </span>
            @endif
        </p>

        <hr>

        <h5>Повідомлення</h5>

        <p>{{ $notification->message }}</p>

        <hr>

        @if (!$notification->is_read && auth()->user()->can('markAsRead', $notification))

        <form action="{{ route('notifications.read', $notification->id) }}"
            method="POST"
            class="d-inline">

            @csrf
            @method('PATCH')

            <button type="submit" class="btn btn-success">
                Позначити як прочитане
            </button>

        </form>

        @endif
        @can('delete', $notification)
        <form action="{{ route('notifications.destroy', $notification->id) }}"
            method="POST"
            class="d-inline"
            onsubmit="return confirm('Видалити сповіщення?');">

            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-danger">
                Видалити
            </button>

        </form>
        @endcan

    </div>
</div>

@endsection