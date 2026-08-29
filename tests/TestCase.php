<?php

namespace Ridho\JustSubs\Tests;

use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as Orchestra;
use Ridho\JustSubs\JustSubsServiceProvider;

class DummyUser extends Model
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
        // Setup default config for testing
        $app['config']->set('app.key', 'base64:Xv9u8X2wY2JmP3x5v9u8X2wY2JmP3x5v9u8X2wY2JmP=');
    }
}
