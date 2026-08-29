<?php

namespace Ridho\JustSubs\Tests\Feature;

use Ridho\JustSubs\Tests\TestCase;
use Ridho\JustSubs\JustSubsServiceProvider;
use Illuminate\Support\Facades\Config;

class ServiceProviderTest extends TestCase
{
    public function test_it_can_boot_the_service_provider()
    {
        $providers = $this->app->getLoadedProviders();
        $this->assertArrayHasKey(JustSubsServiceProvider::class, $providers);
    }

    public function test_it_loads_configuration_file()
    {
        $this->assertEquals('justsubs_plans', Config::get('justsubs.tables.plans'));
        $this->assertEquals('justsubs_subscriptions', Config::get('justsubs.tables.subscriptions'));
        $this->assertEquals('justsubs_invoices', Config::get('justsubs.tables.invoices'));
        $this->assertEquals('justsubs_payments', Config::get('justsubs.tables.payments'));

        $this->assertEquals('justsubs', Config::get('justsubs.route.prefix'));
        $this->assertEquals(['web', 'auth'], Config::get('justsubs.route.middleware'));
    }
}
