<?php

declare(strict_types=1);

use App\Http\Controllers\FavoriteListingController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertySearchController;
use Illuminate\Support\Facades\Route;

// Головна сторінка Laravel
Route::get('/', function () {
    return view('welcome');
});

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
