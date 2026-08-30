<?php

namespace Ridho\JustSubs\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Orchestra\Testbench\TestCase as Orchestra;
use Ridho\JustSubs\JustSubsServiceProvider;

class DummyUser extends Authenticatable
{
    protected $table = 'dash_users';

    protected $guarded = [];
}

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            JustSubsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:Xv9u8X2wY2JmP3x5v9u8X2wY2JmP3x5v9u8X2wY2JmP=');
        $app['config']->set('auth.providers.users.model', DummyUser::class);
    }

    protected function defineRoutes($router)
    {
        $router->get('/login', function () {
            return 'Login';
        })->name('login');
    }
}
