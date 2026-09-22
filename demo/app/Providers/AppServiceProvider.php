<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Ridho\JustSubs\JustSubs;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JustSubs::auth(function ($request) {
            return $request->user() && $request->user()->email === 'admin@justsubs.test';
        });
    }
}
