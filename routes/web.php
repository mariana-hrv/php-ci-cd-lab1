<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteListingController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ModerationRecordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyPhotoController;
use App\Http\Controllers\PropertySearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewingRequestController;
use Illuminate\Support\Facades\Route;

// Головна сторінка Laravel
Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Перевірка стану застосунку
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
    ]);
})->name('health');

// Маршрути системи продажу нерухомості
Route::prefix('properties')->name('properties.')->group(function () {

    // Отримання списку об'єктів
    Route::get('/', [PropertyController::class, 'index'])
        ->name('index');

    // Пошук нерухомості
    Route::get('/search', [PropertySearchController::class, 'search'])
        ->name('search');

    // Створення нового об'єкта з перевіркою доступу
    Route::post('/', [PropertyController::class, 'store'])
        ->middleware('property.access')
        ->name('store');

    // HTML-форма створення нерухомості
    Route::middleware('auth')->group(function () {

        Route::get('/create', [PropertyController::class, 'create'])
            ->name('create');

        Route::post('/create', [PropertyController::class, 'store'])
            ->name('store-form');
    });

    Route::middleware('auth')->group(function () {

        Route::get('/{id}/edit', [PropertyController::class, 'edit'])
            ->whereNumber('id')
            ->name('edit');

        Route::put('/{id}', [PropertyController::class, 'update'])
            ->whereNumber('id')
            ->name('update');

        Route::delete('/{id}', [PropertyController::class, 'destroy'])
            ->whereNumber('id')
            ->name('destroy');
    });

    // Перегляд об'єкта за числовим ID
    Route::get('/{id}', [PropertyController::class, 'show'])
        ->whereNumber('id')
        ->name('show');
}); // Закінчення групи properties

// Маршрути для роботи з вибраними оголошеннями
Route::middleware('auth')
    ->prefix('favorites')
    ->name('favorites.')
    ->group(function () {

        // Отримання вибраних оголошень користувача
        Route::get('/', [
            FavoriteListingController::class,
            'index',
        ])->name('index');

        // Синхронізація вибраних оголошень
        Route::put('/', [
            FavoriteListingController::class,
            'sync',
        ])->name('sync');
    });

// Маршрути для оголошень нерухомості
Route::prefix('listings')->name('listings.')->group(function () {

    // Публічний перегляд оголошень
    Route::get('/', [ListingController::class, 'index'])
        ->name('index');

    Route::get('/create', [ListingController::class, 'create'])
        ->middleware('auth')
        ->name('create');

    Route::get('/{id}/edit', [ListingController::class, 'edit'])
        ->middleware('auth')
        ->name('edit');

    Route::get('/{id}', [ListingController::class, 'show'])
        ->whereNumber('id')
        ->name('show');

    // Створення, редагування та видалення
    // лише для автентифікованих користувачів
    Route::middleware('auth')->group(function () {

        Route::post('/', [ListingController::class, 'store'])
            ->name('store');

        Route::put('/{id}', [ListingController::class, 'update'])
            ->whereNumber('id')
            ->name('update');

        Route::delete('/{id}', [ListingController::class, 'destroy'])
            ->whereNumber('id')
            ->name('destroy');
    });
});

// Маршрути для заявок на перегляд нерухомості
Route::middleware('auth')
    ->prefix('viewing-requests')
    ->name('viewing-requests.')
    ->group(function () {

        // Отримання заявок поточного покупця
        Route::get('/', [
            ViewingRequestController::class,
            'index',
        ])->name('index');

        // Створення нової заявки
        Route::post('/', [
            ViewingRequestController::class,
            'store',
        ])->name('store');

        // Отримання заявок на оголошення продавця
        Route::get('/incoming', [
            ViewingRequestController::class,
            'incoming',
        ])->name('incoming');

        // Підтвердження або відхилення заявки продавцем
        Route::patch('/{id}/decision', [
            ViewingRequestController::class,
            'decide',
        ])->whereNumber('id')->name('decide');

        Route::get('/create', [ViewingRequestController::class, 'create'])
            ->middleware('auth')
            ->name('create');

        Route::get('/{id}/edit', [ViewingRequestController::class, 'edit'])
            ->middleware('auth')
            ->name('edit');

        // Перегляд конкретної заявки
        Route::get('/{id}', [
            ViewingRequestController::class,
            'show',
        ])->whereNumber('id')->name('show');

        // Редагування заявки
        Route::put('/{id}', [
            ViewingRequestController::class,
            'update',
        ])->whereNumber('id')->name('update');

        // Скасування заявки
        Route::delete('/{id}', [
            ViewingRequestController::class,
            'destroy',
        ])->whereNumber('id')->name('destroy');
    });

// Маршрути для фотографій нерухомості

// Публічний перегляд фотографій конкретного об'єкта
Route::get('/properties/{propertyId}/photos', [
    PropertyPhotoController::class,
    'index',
])->whereNumber('propertyId')->name('properties.photos.index');

// Додавання та видалення фотографій
Route::middleware('auth')
    ->prefix('property-photos')
    ->name('property-photos.')
    ->group(function () {

        // Додавання фотографії
        Route::post('/', [
            PropertyPhotoController::class,
            'store',
        ])->name('store');

        // Видалення фотографії
        Route::delete('/{id}', [
            PropertyPhotoController::class,
            'destroy',
        ])->whereNumber('id')->name('destroy');
    });

// Маршрути для сповіщень користувачів
Route::middleware('auth')
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function () {

        // Отримання всіх сповіщень користувача
        Route::get('/', [
            NotificationController::class,
            'index',
        ])->name('index');

        // Перегляд конкретного сповіщення
        Route::get('/{id}', [
            NotificationController::class,
            'show',
        ])->whereNumber('id')->name('show');

        // Позначення сповіщення як прочитаного
        Route::patch('/{id}/read', [
            NotificationController::class,
            'markAsRead',
        ])->whereNumber('id')->name('read');

        // Видалення сповіщення
        Route::delete('/{id}', [
            NotificationController::class,
            'destroy',
        ])->whereNumber('id')->name('destroy');
    });

// Маршрути для модерації оголошень
Route::middleware('auth')
    ->prefix('moderation-records')
    ->name('moderation-records.')
    ->group(function () {

        // Перегляд історії модерації
        Route::get('/', [
            ModerationRecordController::class,
            'index',
        ])->name('index');

        Route::get('/create', [ModerationRecordController::class, 'create'])
            ->middleware('auth')
            ->name('create');

        // Перегляд конкретного рішення
        Route::get('/{id}', [
            ModerationRecordController::class,
            'show',
        ])->whereNumber('id')->name('show');

        // Схвалення або відхилення оголошення
        Route::post('/', [
            ModerationRecordController::class,
            'store',
        ])->name('store');
    });

Route::middleware('auth')->group(function () {

    Route::get('/profile', [UserController::class, 'profile'])
        ->name('profile');

    Route::get('/profile/edit', [UserController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [UserController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [UserController::class, 'destroy'])
        ->name('profile.destroy');

    Route::prefix('users')->name('users.')->group(function () {

        Route::get('/', [UserController::class, 'index'])
            ->name('index');

        Route::get('/{id}', [UserController::class, 'show'])
            ->whereNumber('id')
            ->name('show');
    });
});
