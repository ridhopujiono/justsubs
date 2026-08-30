<?php

namespace Ridho\JustSubs;

use Illuminate\Support\ServiceProvider;
use Ridho\JustSubs\Console\InstallCommand;
use Ridho\JustSubs\Console\StatusCommand;

class JustSubsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/justsubs.php', 'justsubs'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/justsubs.php' => config_path('justsubs.php'),
            ], 'justsubs-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'justsubs-migrations');

            $this->commands([
                InstallCommand::class,
                StatusCommand::class,
            ]);

            // Publish public assets
            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/justsubs'),
            ], 'justsubs-assets');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'justsubs');
    }
}
