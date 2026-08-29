<?php

use Illuminate\Support\Facades\Route;

Route::middleware(array_merge(
    config('justsubs.route.middleware', ['web', 'auth']),
    [\Ridho\JustSubs\Http\Middleware\Authorize::class]
))
->prefix(config('justsubs.route.prefix', 'justsubs'))
->name('justsubs.')
->group(function () {
    Route::get('/', function () {
        return 'JustSubs Dashboard';
    })->name('dashboard');
});
