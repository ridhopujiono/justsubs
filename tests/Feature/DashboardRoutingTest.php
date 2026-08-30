<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Tests\TestCase;

class DashboardRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        JustSubs::$authUsing = null;
        parent::tearDown();
    }

    protected function defineEnvironmentForTesting($app)
    {
        $app['config']->set('justsubs.route.middleware', ['web']);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_dashboard_is_unauthorized_by_default_in_testing_env()
    {
        // The default auth checks app()->environment('local').
        // In testing, environment is usually 'testing', so it should return 403.
        $this->get('/justsubs')->assertStatus(403);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_dashboard_is_authorized_with_custom_callback()
    {
        JustSubs::auth(function ($request) {
            return true;
        });

        $this->get('/justsubs')
            ->assertStatus(200)
            ->assertSee('JustSubs Dashboard');
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_dashboard_is_authorized_by_default_in_local_env()
    {
        $this->app['env'] = 'local';

        $this->get('/justsubs')
            ->assertStatus(200)
            ->assertSee('JustSubs Dashboard');
    }

    protected function defineEnvironmentForPrefix($app)
    {
        $app['config']->set('justsubs.route.prefix', 'admin/subs');
    }

    #[DefineEnvironment('defineEnvironmentForPrefix')]
    public function test_route_uses_configured_prefix()
    {
        $this->assertEquals(url('admin/subs'), route('justsubs.dashboard'));
    }
}
