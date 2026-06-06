<?php
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are versioned under /api/v1/
|
*/
Broadcast::routes(['middleware' => ['web', 'auth']]);
Route::prefix('v1')->group(function () {

    // ─── Public Auth Routes ───────────────────────────────────────────────────

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->name('api.v1.auth.register');
        Route::post('/login', [AuthController::class, 'login'])
            ->name('api.v1.auth.login');
    });

    // ─── Public Availability Check ────────────────────────────────────────────

    Route::post('/availability/check', [\App\Http\Controllers\Api\V1\VehicleController::class, 'checkAvailability'])
        ->name('api.v1.availability.check');

    // ─── Chatbot Routes (public — no auth required) ───────────────────────────

    Route::prefix('chatbot')->group(function () {
        Route::get('/health', [\App\Http\Controllers\Api\V1\ChatbotController::class, 'health'])
            ->name('api.v1.chatbot.health');
        Route::post('/message', [\App\Http\Controllers\Api\V1\ChatbotController::class, 'message'])
            ->name('api.v1.chatbot.message');
        Route::get('/history/{sessionId}', [\App\Http\Controllers\Api\V1\ChatbotController::class, 'history'])
            ->name('api.v1.chatbot.history');
        Route::delete('/history/{sessionId}', [\App\Http\Controllers\Api\V1\ChatbotController::class, 'clearHistory'])
            ->name('api.v1.chatbot.clear');
    });

    // ─── Chat Routes (session + sanctum auth — works for admin and customer) ──

    Route::middleware('web', 'auth')->prefix('chat')->group(function () {
        Route::get('/room', [\App\Http\Controllers\Api\V1\ChatController::class, 'room'])
            ->name('api.v1.chat.room');
        Route::get('/rooms', [\App\Http\Controllers\Api\V1\ChatController::class, 'rooms'])
            ->name('api.v1.chat.rooms');
        Route::get('/rooms/{chatRoom}/messages', [\App\Http\Controllers\Api\V1\ChatController::class, 'messages'])
            ->name('api.v1.chat.messages');
        Route::post('/rooms/{chatRoom}/messages', [\App\Http\Controllers\Api\V1\ChatController::class, 'sendMessage'])
            ->name('api.v1.chat.send');
        Route::post('/rooms/{chatRoom}/read', [\App\Http\Controllers\Api\V1\ChatController::class, 'markRead'])
            ->name('api.v1.chat.read');
    });

    // ─── Protected Routes (Sanctum) ───────────────────────────────────────────

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('api.v1.auth.logout');
            Route::get('/me', [AuthController::class, 'me'])
                ->name('api.v1.auth.me');
        });

        // ─── Payments ─────────────────────────────────────────────────────────

        Route::post('/payments/bank-transfer/initiate', [\App\Http\Controllers\Api\V1\PaymentController::class, 'initiateBankTransfer'])
            ->name('api.v1.payments.initiate');
        Route::post('/payments/{payment}/upload-receipt', [\App\Http\Controllers\Api\V1\PaymentController::class, 'uploadReceipt'])
            ->name('api.v1.payments.upload-receipt');
        Route::get('/payments/{payment}', [\App\Http\Controllers\Api\V1\PaymentController::class, 'show'])
            ->name('api.v1.payments.show');
        Route::get('/payments/{payment}/invoice', [\App\Http\Controllers\Api\V1\PaymentController::class, 'invoice'])
            ->name('api.v1.payments.invoice');

        // ─── Customer Routes ──────────────────────────────────────────────────

        Route::middleware('ensure.customer')->group(function () {

            // Vehicles
            Route::get('/vehicles', [\App\Http\Controllers\Api\V1\VehicleController::class, 'index'])
                ->name('api.v1.vehicles.index');
            Route::get('/vehicles/{vehicle}', [\App\Http\Controllers\Api\V1\VehicleController::class, 'show'])
                ->name('api.v1.vehicles.show');

            // Bookings
            Route::get('/bookings', [\App\Http\Controllers\Api\V1\BookingController::class, 'index'])
                ->name('api.v1.bookings.index');
            Route::post('/bookings', [\App\Http\Controllers\Api\V1\BookingController::class, 'store'])
                ->name('api.v1.bookings.store');
            Route::get('/bookings/{booking}', [\App\Http\Controllers\Api\V1\BookingController::class, 'show'])
                ->name('api.v1.bookings.show');
            Route::patch('/bookings/{booking}/cancel', [\App\Http\Controllers\Api\V1\BookingController::class, 'cancel'])
                ->name('api.v1.bookings.cancel');

            // GPS
            Route::post('/gps/update', [\App\Http\Controllers\Api\V1\GpsController::class, 'update'])
                ->name('api.v1.gps.update');
            Route::get('/gps/vehicles/{vehicle}/live', [\App\Http\Controllers\Api\V1\GpsController::class, 'liveLocation'])
                ->name('api.v1.gps.live');
            Route::get('/gps/vehicles/{vehicle}/history', [\App\Http\Controllers\Api\V1\GpsController::class, 'history'])
                ->name('api.v1.gps.history');
            Route::get('/gps/active-locations', [\App\Http\Controllers\Api\V1\GpsController::class, 'activeLocations'])
                ->name('api.v1.gps.active');
        });

        // ─── Admin Routes ─────────────────────────────────────────────────────

        Route::middleware('ensure.admin')->prefix('admin')->group(function () {

            // Vehicles
            Route::apiResource('vehicles', \App\Http\Controllers\Api\V1\VehicleController::class)
                ->names('api.v1.admin.vehicles');

            Route::get('vehicle-categories', [\App\Http\Controllers\Api\V1\VehicleController::class, 'index'])
                ->name('api.v1.admin.vehicle-categories.index');

            // Bookings
            Route::get('bookings', [\App\Http\Controllers\Api\V1\BookingController::class, 'index'])
                ->name('api.v1.admin.bookings.index');
            Route::get('bookings/{booking}', [\App\Http\Controllers\Api\V1\BookingController::class, 'show'])
                ->name('api.v1.admin.bookings.show');
            Route::patch('bookings/{booking}/status', [\App\Http\Controllers\Api\V1\BookingController::class, 'updateStatus'])
                ->name('api.v1.admin.bookings.status');

            // Payments
            Route::get('payments', [\App\Http\Controllers\Api\V1\PaymentController::class, 'show'])
                ->name('api.v1.admin.payments.index');
        });
    });
});