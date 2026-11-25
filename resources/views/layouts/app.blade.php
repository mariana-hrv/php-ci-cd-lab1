<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Нерухомість')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">

            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                Нерухомість
            </a>

            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu"
                aria-controls="navbarMenu"
                aria-expanded="false"
                aria-label="Відкрити меню">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            Головна
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('properties.index') }}">
                            Об'єкти нерухомості
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('listings.index') }}">
                            Оголошення
                        </a>
                    </li>

                    @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('favorites.index') }}">
                            Вибране
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('viewing-requests.index') }}">
                            Заявки
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('notifications.index') }}">
                            Сповіщення
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile') }}">
                            Особистий профіль
                        </a>
                    </li>

                    @if (auth()->user()->isAdmin())

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('moderation-records.index') }}">
                            Модерація
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">
                            Користувачі
                        </a>
                    </li>

                    @endif
                    @endauth

                </ul>
                <div class="d-flex align-items-center gap-2">

                    @guest

                    <a href="{{ route('login') }}"
                        class="btn btn-outline-light">
                        Увійти
                    </a>

                    <a href="{{ route('register') }}"
                        class="btn btn-primary">
                        Реєстрація
                    </a>

                    @endguest

                    @auth

                    <span class="text-light me-2">
                        {{ auth()->user()->name }}
                    </span>

                    <form action="{{ route('logout') }}" method="POST">

                        @csrf

                        <button type="submit" class="btn btn-outline-light">
                            Вийти
                        </button>

                    </form>

                    @endauth

                </div>
            </div>
        </div>
    </nav>

    <main class="container">

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}

            <button type="button" class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Закрити"></button>
        </div>
        @endif

        @yield('content')

    </main>

    <footer class="container text-center text-muted border-top mt-5 py-3">
        Електронна система продажу нерухомості
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>