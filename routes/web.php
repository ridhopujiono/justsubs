<?php

use Illuminate\Support\Facades\Route;
use Ridho\JustSubs\Http\Controllers\DashboardController;
use Ridho\JustSubs\Http\Controllers\InvoiceController;
use Ridho\JustSubs\Http\Controllers\PaymentController;
use Ridho\JustSubs\Http\Controllers\PlanController;
use Ridho\JustSubs\Http\Controllers\SubscriberController;
use Ridho\JustSubs\Http\Controllers\SubscriptionController;
use Ridho\JustSubs\Http\Middleware\Authorize;

Route::middleware(array_merge(
    config('justsubs.route.middleware', ['web', 'auth']),
    [Authorize::class]
))
    ->prefix(config('justsubs.route.prefix', 'justsubs'))
    ->name('justsubs.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Plans
        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');

        // Subscriptions
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
        Route::post('/subscriptions/{subscription}/extend', [SubscriptionController::class, 'extend'])->name('subscriptions.extend');
        Route::post('/subscriptions/{subscription}/change-plan', [SubscriptionController::class, 'changePlan'])->name('subscriptions.change_plan');
        Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark_paid');
        Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

        // Subscribers
        Route::get('/subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
    });
