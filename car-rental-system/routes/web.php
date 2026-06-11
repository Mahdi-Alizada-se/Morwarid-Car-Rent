<?php
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ─── Traccar Webhook (no auth, no CSRF) ──────────────────────────────────────
Broadcast::routes(['middleware' => ['web', 'auth']]);
Route::match(['GET', 'POST'], '/webhooks/traccar', [\App\Http\Controllers\Webhooks\TraccarWebhookController::class, 'handle'])
    ->name('webhooks.traccar');

// ─── GPS Tracker Route (no auth — runs on Samsung phone in car) ───────────────

Route::get('/track/{vehicle}/{token}', [\App\Http\Controllers\GpsTrackerController::class, 'track'])
    ->name('gps.tracker');

// ─── Guest Routes ─────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->name('social.redirect')
        ->where('provider', 'google|facebook');

    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->name('social.callback')
        ->where('provider', 'google|facebook');
});

// ─── Public Vehicle Routes ────────────────────────────────────────────────────

Route::get('/vehicles', [\App\Http\Controllers\Customer\VehicleController::class, 'index'])
    ->name('vehicles.index');

Route::get('/vehicles/{vehicle}', [\App\Http\Controllers\Customer\VehicleController::class, 'show'])
    ->name('vehicles.show');

// ─── Authenticated Routes ─────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');

    // Booking confirmed — accessible by both customer and admin
    Route::get('/bookings/{booking}/confirmed', [\App\Http\Controllers\Customer\BookingController::class, 'confirmed'])
        ->name('bookings.confirmed');

    // Cash pending page — accessible by customer
    Route::get('/bookings/{booking}/cash-pending', [\App\Http\Controllers\Customer\BookingController::class, 'cashPending'])
        ->name('bookings.cash-pending');

    // ─── Admin Area ───────────────────────────────────────────────────────────

    Route::middleware('ensure.admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Dashboard
            Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
                ->name('dashboard');

            // Dashboard API endpoints
            Route::get('/api/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats']);
            Route::get('/api/charts/revenue', [\App\Http\Controllers\Admin\DashboardController::class, 'revenueChart']);
            Route::get('/api/charts/bookings', [\App\Http\Controllers\Admin\DashboardController::class, 'bookingsChart']);

            // Reports
            Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])
                ->name('reports');
            Route::get('/reports/export/csv', [\App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])
                ->name('reports.csv');
            Route::get('/reports/export/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])
                ->name('reports.pdf');

            // Vehicles
            Route::resource('vehicles', \App\Http\Controllers\Admin\VehicleController::class);
            Route::patch('vehicles/{vehicle}/toggle-status', [\App\Http\Controllers\Admin\VehicleController::class, 'toggleStatus'])
                ->name('vehicles.toggle-status');
            Route::post('vehicles/{vehicle}/regenerate-token', [\App\Http\Controllers\Admin\VehicleController::class, 'regenerateToken'])
                ->name('vehicles.regenerate-token');

            // Bookings
            Route::get('bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])
                ->name('bookings.index');
            Route::get('bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])
                ->name('bookings.show');
            Route::patch('bookings/{booking}/status', [\App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])
                ->name('bookings.update-status');
            Route::get('bookings/calendar', [\App\Http\Controllers\Admin\BookingController::class, 'calendarData'])
                ->name('bookings.calendar');

            // Payments
            Route::get('payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])
                ->name('payments.index');
            Route::get('payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])
                ->name('payments.show');
            Route::post('payments/{payment}/confirm', [\App\Http\Controllers\Admin\PaymentController::class, 'confirm'])
                ->name('payments.confirm');
            Route::post('payments/{payment}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])
                ->name('payments.reject');
            Route::post('payments/counter', [\App\Http\Controllers\Admin\PaymentController::class, 'counterPayment'])
                ->name('payments.counter');

            // Chat
            Route::get('chat', [\App\Http\Controllers\Admin\ChatController::class, 'index'])
                ->name('chat.index');

            // GPS
            Route::prefix('gps')->name('gps.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\GpsController::class, 'index'])
                    ->name('index');
                Route::get('/vehicles/{vehicle}/history', [\App\Http\Controllers\Admin\GpsController::class, 'history'])
                    ->name('history');
                Route::get('/vehicles/{vehicle}/setup', [\App\Http\Controllers\Admin\GpsController::class, 'setup'])
                    ->name('setup');
                Route::post('/vehicles/{vehicle}/device', [\App\Http\Controllers\Admin\GpsController::class, 'saveDevice'])
                    ->name('save-device');
                Route::delete('/vehicles/{vehicle}/device', [\App\Http\Controllers\Admin\GpsController::class, 'removeDevice'])
                    ->name('remove-device');
            });

            // Users
            Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])
                ->name('users.index');
            Route::post('users/{user}/verify-license', [\App\Http\Controllers\Admin\UserController::class, 'verifyLicense'])
                ->name('users.verify-license');
            Route::get('users/{user}/license', [\App\Http\Controllers\Admin\UserController::class, 'showLicense'])
                ->name('users.license');

        });

    // ─── Customer Area ────────────────────────────────────────────────────────

    Route::middleware('ensure.customer')
        ->name('customer.')
        ->group(function () {

            // Bookings
            Route::get('/my-bookings', [\App\Http\Controllers\Customer\BookingController::class, 'index'])
                ->name('bookings.index');
            Route::get('/bookings/create', [\App\Http\Controllers\Customer\BookingController::class, 'create'])
                ->name('bookings.create');
            Route::post('/bookings', [\App\Http\Controllers\Customer\BookingController::class, 'store'])
                ->name('bookings.store');
            Route::get('/bookings/{booking}', [\App\Http\Controllers\Customer\BookingController::class, 'show'])
                ->name('bookings.show');
            Route::patch('/bookings/{booking}/cancel', [\App\Http\Controllers\Customer\BookingController::class, 'cancel'])
                ->name('bookings.cancel');

            // Payments
            Route::get('/payments/{booking}/checkout', [\App\Http\Controllers\Customer\PaymentController::class, 'checkout'])
                ->name('payments.checkout');
            Route::get('/payments/{payment}/status', [\App\Http\Controllers\Customer\PaymentController::class, 'status'])
                ->name('payments.status');
            Route::post('/payments/counter', [\App\Http\Controllers\Customer\PaymentController::class, 'counter'])
                ->name('payments.counter');
            Route::post('/payments/bank-transfer', [\App\Http\Controllers\Customer\PaymentController::class, 'initiateBankTransfer'])
                ->name('payments.bank-transfer');
            Route::post('/payments/{booking}/cash', [\App\Http\Controllers\Customer\PaymentController::class, 'cashPayment'])
                ->name('payments.cash');
            Route::post('/payments/{booking}/online/confirm', [\App\Http\Controllers\Customer\PaymentController::class, 'onlineConfirm'])
                ->name('payments.online.confirm');

        });

});

// ─── Language Switcher ────────────────────────────────────────────────────────

Route::post('/language/switch', function () {
    $locale = request('locale');
    $supported = ['en', 'fa'];

    if (in_array($locale, $supported)) {
        session(['locale' => $locale]);

        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
    }

    return redirect()->back();
})->name('language.switch');

// ─── Root Redirect ────────────────────────────────────────────────────────────

Route::get('/', fn() => redirect()->route('vehicles.index'));


// About & Contact pages
Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');