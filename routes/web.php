<?php

use App\Http\Controllers\SecretController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*rate limit*/
RateLimiter::for('secret-creation', static function (Request $request) {
    if (app()->environment('testing')) {
        return Limit::perMinute(1000)->by($request->ip());
    }

    return Limit::perMinute(10)->by($request->ip());
});

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
*/
Route::get('/', [SecretController::class, 'create'])->name('home');
Route::get('/secret', [SecretController::class, 'create'])->name('secret.create');
Route::post('/secret', [SecretController::class, 'store'])->name('secret.store')->middleware('throttle:secret-creation');
Route::get('/secret/expired', [SecretController::class, 'expired'])->name('secret.expired');
Route::get('/secret/{token}/created', [SecretController::class, 'created'])->name('secret.created');
Route::get('/secret/{token}', [SecretController::class, 'show'])->name('secret.show');
