<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'Backend is connected']);
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public property routes
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/featured', [PropertyController::class, 'featured']);
Route::get('/properties/{property}', [PropertyController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Properties
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::put('/properties/{property}', [PropertyController::class, 'update']);
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/properties/{property}/favorite', [FavoriteController::class, 'toggle']);

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

    // Appointment custom actions
    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm']);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete']);
    Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);
    Route::get('/appointments/statistics', [AppointmentController::class, 'statistics']);

    // Inquiries
    Route::post('/properties/{property}/inquiry', [InquiryController::class, 'store']);
    Route::get('/inquiries', [InquiryController::class, 'index'])->middleware('can:view,App\Models\Inquiry');

    // User management
    Route::put('/profile', [UserController::class, 'updateProfile']);

    //Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Admin only
        Route::get('/agents', [UserController::class, 'agents']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}/role', [UserController::class, 'updateRole']); // Admin only
        Route::put('/{user}/toggle-active', [UserController::class, 'toggleActive']); // Admin only
    });

    // User profile routes
    Route::put('/profile', [UserController::class, 'updateProfile']);

    Route::get('/profile/{user}', [UserController::class, 'show'])->name('profile.show');
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar']);
    Route::delete('/profile/avatar', [UserController::class, 'deleteAvatar']);
    Route::post('/profile/change-password', [UserController::class, 'changePassword']);
    Route::get('/profile/statistics', [UserController::class, 'statistics']);
});
