@extends('layouts.app')

@section('title', 'Мої сповіщення')

@section('content')

<h1 class="mb-4">Мої сповіщення</h1>

<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Повідомлення</th>
                <th>Дата створення</th>
                <th>Статус</th>
                <th>Дії</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($notifications as $notification)

            <tr>
                <td>{{ $notification->id }}</td>

                <td>{{ $notification->message }}</td>

                <td>
                    {{ $notification->created_at?->format('d.m.Y H:i') }}
                </td>

                <td>
                    @if ($notification->is_read)
                    <span class="badge bg-success">
                        Прочитано
                    </span>
                    @else
                    <span class="badge bg-warning text-dark">
                        Не прочитано
                    </span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('notifications.show', $notification->id) }}"
                        class="btn btn-sm btn-info">
                        Перегляд
                    </a>

                    @if (!$notification->is_read && auth()->user()->can('markAsRead', $notification))

                    <form action="{{ route('notifications.read', $notification->id) }}"
                        method="POST"
                        class="d-inline">

                        @csrf
                        @method('PATCH')

                        <button type="submit"
                            class="btn btn-sm btn-success">
                            Прочитано
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

                        <button type="submit"
                            class="btn btn-sm btn-danger">
                            Видалити
                        </button>

                    </form>
                    @endcan

                </td>
            </tr>

            @empty

            <tr>
                <td colspan="5" class="text-center text-muted">
                    У вас поки немає сповіщень.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection