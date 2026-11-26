<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FavoriteApiController;
use App\Http\Controllers\Api\V1\ListingApiController;
use App\Http\Controllers\Api\V1\ModerationRecordApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use App\Http\Controllers\Api\V1\PropertyApiController;
use App\Http\Controllers\Api\V1\PropertyPhotoApiController;
use App\Http\Controllers\Api\V1\UserApiController;
use App\Http\Controllers\Api\V1\ViewingRequestApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Автентифікація
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Публічні маршрути оголошень
    Route::get('/listings', [ListingApiController::class, 'index']);
    Route::get('/listings/{listing}', [ListingApiController::class, 'show']);

    // Публічні маршрути нерухомості
    Route::get('/properties', [PropertyApiController::class, 'index']);
    Route::get('/properties/{property}', [PropertyApiController::class, 'show']);

    // Публічні маршрути фотографій
    Route::get('/properties/{property}/photos', [PropertyPhotoApiController::class, 'index']);
    Route::get('/photos/{photo}', [PropertyPhotoApiController::class, 'show']);

    // Захищені маршрути
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Вибрані оголошення
        Route::get('/favorites', [FavoriteApiController::class, 'index']);
        Route::put('/favorites', [FavoriteApiController::class, 'sync']);

        // Заявки на перегляд нерухомості
        Route::get('/viewing-requests/incoming', [ViewingRequestApiController::class, 'incoming']);
        Route::get('/viewing-requests', [ViewingRequestApiController::class, 'index']);
        Route::post('/viewing-requests', [ViewingRequestApiController::class, 'store']);
        Route::get('/viewing-requests/{viewingRequest}', [ViewingRequestApiController::class, 'show']);
        Route::put('/viewing-requests/{viewingRequest}', [ViewingRequestApiController::class, 'update']);
        Route::delete('/viewing-requests/{viewingRequest}', [ViewingRequestApiController::class, 'destroy']);
        Route::patch('/viewing-requests/{viewingRequest}/decide', [ViewingRequestApiController::class, 'decide']);

        // Сповіщення
        Route::get('/notifications', [NotificationApiController::class, 'index']);
        Route::get('/notifications/{notification}', [NotificationApiController::class, 'show']);
        Route::patch('/notifications/{notification}/read', [NotificationApiController::class, 'markAsRead']);
        Route::delete('/notifications/{notification}', [NotificationApiController::class, 'destroy']);

        // Модерація оголошень
        Route::get('/moderation-records', [ModerationRecordApiController::class, 'index']);
        Route::get('/moderation-records/{moderationRecord}', [ModerationRecordApiController::class, 'show']);
        Route::post('/moderation-records', [ModerationRecordApiController::class, 'store']);

        // Користувачі
        Route::get('/users', [UserApiController::class, 'index']);
        Route::get('/users/{user}', [UserApiController::class, 'show']);

        // Особистий профіль
        Route::get('/profile', [UserApiController::class, 'profile']);
        Route::put('/profile', [UserApiController::class, 'update']);
        Route::delete('/profile', [UserApiController::class, 'destroy']);

        // Оголошення
        Route::post('/listings', [ListingApiController::class, 'store']);
        Route::put('/listings/{listing}', [ListingApiController::class, 'update']);
        Route::delete('/listings/{listing}', [ListingApiController::class, 'destroy']);

        // Нерухомість
        Route::post('/properties', [PropertyApiController::class, 'store']);
        Route::put('/properties/{property}', [PropertyApiController::class, 'update']);
        Route::delete('/properties/{property}', [PropertyApiController::class, 'destroy']);

        // Фотографії нерухомості
        Route::post('/photos', [PropertyPhotoApiController::class, 'store']);
        Route::delete('/photos/{photo}', [PropertyPhotoApiController::class, 'destroy']);
    });
});
