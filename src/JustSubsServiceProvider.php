<?php

namespace Ridho\JustSubs;

use Illuminate\Support\ServiceProvider;

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
                \Ridho\JustSubs\Console\InstallCommand::class,
                \Ridho\JustSubs\Console\StatusCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
