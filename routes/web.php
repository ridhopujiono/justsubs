<?php

use Illuminate\Support\Facades\Route;

Route::middleware(array_merge(
    config('justsubs.route.middleware', ['web', 'auth']),
    [\Ridho\JustSubs\Http\Middleware\Authorize::class]
))
->prefix(config('justsubs.route.prefix', 'justsubs'))
->name('justsubs.')
->group(function () {
    Route::get('/', [\Ridho\JustSubs\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Stub routes for navigation
    Route::get('/plans', function () { return 'Plans'; })->name('plans.index');
    Route::get('/subscriptions', function () { return 'Subscriptions'; })->name('subscriptions.index');
    Route::get('/subscribers', function () { return 'Subscribers'; })->name('subscribers.index');
    Route::get('/invoices', function () { return 'Invoices'; })->name('invoices.index');
    Route::get('/payments', function () { return 'Payments'; })->name('payments.index');
});
