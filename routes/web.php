<?php

declare(strict_types=1);

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
});
