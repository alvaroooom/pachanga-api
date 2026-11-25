<?php
require __DIR__ . '/auth.php';

use App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// ---------- Panel de administracion (solo usuarios con rol admin) ----------
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('stats', [Admin\StatsController::class, 'index']);

    Route::patch('users/{user}/role', [Admin\UserController::class, 'role']);
    Route::apiResource('users', Admin\UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::apiResource('venues', Admin\VenueController::class);

    Route::patch('games/{game}/cancel', [Admin\GameController::class, 'cancel']);
    Route::apiResource('games', Admin\GameController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('games/{game}/registrations', [Admin\RegistrationController::class, 'index']);
    Route::apiResource('registrations', Admin\RegistrationController::class)->only(['update', 'destroy']);

    Route::apiResource('ratings', Admin\RatingController::class)->only(['index', 'show', 'destroy']);
    Route::apiResource('posts', Admin\PostController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('comments', Admin\CommentController::class)->only(['index', 'destroy']);
});
