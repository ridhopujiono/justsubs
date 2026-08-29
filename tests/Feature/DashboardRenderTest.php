<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Tests\TestCase;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Schema::create('dash_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    protected function defineEnvironmentForTesting($app)
    {
        $app['config']->set('justsubs.route.middleware', ['web']);
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_dashboard_renders_with_correct_data()
    {
        JustSubs::auth(function () { return true; });

        // Seed some data
        Plan::factory()->count(3)->create();
        
        Subscription::factory()->create([
            'subscriber_type' => 'App\Models\User',
            'subscriber_id' => '1',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(20),
        ]);
        
        Subscription::factory()->create([
            'subscriber_type' => 'App\Models\User',
            'subscriber_id' => '2',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(3), // Expiring soon
        ]);

        $response = $this->get('/justsubs');
        
        $response->assertStatus(200);
        $response->assertSee('Overview');
        $response->assertSee('3'); // Total plans
        $response->assertSee('2'); // Active subscriptions
        $response->assertSee('1'); // Expiring soon
        
        // Assert layout renders the sidebar links
        $response->assertSee(route('justsubs.plans.index'));
        $response->assertSee(route('justsubs.subscriptions.index'));
        $response->assertSee(route('justsubs.subscribers.index'));
        $response->assertSee(route('justsubs.invoices.index'));
        $response->assertSee(route('justsubs.payments.index'));

        JustSubs::$authUsing = null;
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_navigation_routes_are_correct()
    {
        JustSubs::auth(function () { return true; });

        $this->get('/justsubs/plans')->assertSee('Plans');
        $this->get('/justsubs/subscriptions')->assertSee('Subscriptions');
        $this->get('/justsubs/subscribers')->assertSee('Subscribers');
        $this->get('/justsubs/invoices')->assertSee('Invoices');
        $this->get('/justsubs/payments')->assertSee('Payments');

        JustSubs::$authUsing = null;
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_unauthorized_dashboard_is_blocked()
    {
        $this->get('/justsubs')->assertStatus(403);
    }
}
