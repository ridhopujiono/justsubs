<?php

namespace Ridho\JustSubs\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Ridho\JustSubs\JustSubsServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            JustSubsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set any environment config here
    }
}
