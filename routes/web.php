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
    
    // Plans
    Route::get('/plans', [\Ridho\JustSubs\Http\Controllers\PlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [\Ridho\JustSubs\Http\Controllers\PlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [\Ridho\JustSubs\Http\Controllers\PlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [\Ridho\JustSubs\Http\Controllers\PlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [\Ridho\JustSubs\Http\Controllers\PlanController::class, 'update'])->name('plans.update');
    
    // Subscriptions
    Route::get('/subscriptions', [\Ridho\JustSubs\Http\Controllers\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [\Ridho\JustSubs\Http\Controllers\SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}/renew', [\Ridho\JustSubs\Http\Controllers\SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('/subscriptions/{subscription}/extend', [\Ridho\JustSubs\Http\Controllers\SubscriptionController::class, 'extend'])->name('subscriptions.extend');
    Route::post('/subscriptions/{subscription}/change-plan', [\Ridho\JustSubs\Http\Controllers\SubscriptionController::class, 'changePlan'])->name('subscriptions.change_plan');
    Route::post('/subscriptions/{subscription}/cancel', [\Ridho\JustSubs\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    
    // Stub routes for other navigation
    Route::get('/subscribers', function () { return 'Subscribers'; })->name('subscribers.index');
    Route::get('/invoices', function () { return 'Invoices'; })->name('invoices.index');
    Route::get('/payments', function () { return 'Payments'; })->name('payments.index');
});
