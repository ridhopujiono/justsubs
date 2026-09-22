<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Tests\DummyUser;
use Ridho\JustSubs\Tests\TestCase;

class SubscriberDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('dash_users', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        JustSubs::auth(function () {
            return true;
        });
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_view_subscribers_list()
    {
        $user = DummyUser::create(['name' => 'Demo User']);
        $plan = Plan::factory()->create();

        Subscription::factory()->create([
            'subscriber_type' => get_class($user),
            'subscriber_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $response = $this->get('/justsubs/subscribers');

        $response->assertStatus(200);
        $response->assertSee('Demo User');
    }

    protected function defineEnvironmentForTesting($app)
    {
        $app['config']->set('justsubs.route.middleware', ['web']);
    }
}
