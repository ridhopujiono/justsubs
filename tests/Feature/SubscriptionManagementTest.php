<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Tests\DummyUser;
use Ridho\JustSubs\Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('dash_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        // Add a mock resolver for the tests
        JustSubs::resolveSubscriberNameUsing(function ($subscriber) {
            return $subscriber->name ?? 'Custom Resolved Name';
        });

        JustSubs::auth(function () {
            return true;
        });
    }

    protected function tearDown(): void
    {
        JustSubs::$authUsing = null;
        JustSubs::$subscriberNameResolver = null;
        parent::tearDown();
    }

    protected function defineEnvironmentForTesting($app)
    {
        $app['config']->set('justsubs.route.middleware', ['web']);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_list_and_filter_subscriptions()
    {
        $plan1 = Plan::factory()->create(['name' => 'Plan A']);
        $plan2 = Plan::factory()->create(['name' => 'Plan B']);

        // Insert a dummy user directly using DB facade since we don't have a model
        DB::table('dash_users')->insert(['id' => 1, 'name' => 'John Doe']);

        $sub1 = Subscription::factory()->create([
            'plan_id' => $plan1->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => '1',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        $sub2 = Subscription::factory()->create([
            'plan_id' => $plan2->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => '2',
            'status' => SubscriptionStatus::Cancelled,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDays(1),
        ]);

        // Unfiltered list
        $response = $this->get('/justsubs/subscriptions');
        $response->assertStatus(200);
        $response->assertSee('Plan A');
        $response->assertSee('Plan B');
        $response->assertSee('John Doe');

        // Filter by status
        $response = $this->get('/justsubs/subscriptions?status=active');
        $response->assertSee('John Doe'); // Plan A
        $response->assertDontSee('Custom Resolved Name'); // Plan B

        // Filter by plan_id
        $response = $this->get('/justsubs/subscriptions?plan_id='.$plan2->id);
        $response->assertDontSee('John Doe');
        $response->assertSee('Custom Resolved Name');

        // Filter by subscriber_id
        $response = $this->get('/justsubs/subscriptions?subscriber_id=1');
        $response->assertSee('John Doe');
        $response->assertDontSee('Custom Resolved Name');
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_show_subscription_details()
    {
        $plan = Plan::factory()->create(['name' => 'Plan A']);
        $sub = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => '123',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        $response = $this->get('/justsubs/subscriptions/'.$sub->id);
        $response->assertStatus(200);
        $response->assertSee('Plan A');
        $response->assertSee('123');
        $response->assertSee('Renew');
        $response->assertSee('Cancel');
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_renew_subscription_via_http()
    {
        $plan = Plan::factory()->create([
            'interval_count' => 1,
            'interval_unit' => 'month',
        ]);

        $sub = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => '1',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        $originalEndsAt = clone $sub->ends_at;

        $response = $this->post("/justsubs/subscriptions/{$sub->id}/renew");
        $response->assertRedirect("/justsubs/subscriptions/{$sub->id}");

        $sub->refresh();
        $this->assertTrue($sub->ends_at->gt($originalEndsAt));
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_extend_subscription_via_http()
    {
        $plan = Plan::factory()->create();
        $sub = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => '1',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        $originalEndsAt = clone $sub->ends_at;

        $response = $this->post("/justsubs/subscriptions/{$sub->id}/extend", [
            'count' => 2,
            'unit' => 'week',
            'reason' => 'Good customer',
        ]);

        $response->assertRedirect("/justsubs/subscriptions/{$sub->id}");

        $sub->refresh();
        $this->assertEquals(
            $originalEndsAt->addWeeks(2)->toDateTimeString(),
            $sub->ends_at->toDateTimeString()
        );
        $this->assertEquals('Good customer', $sub->metadata['extensions'][0]['reason']);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_change_plan_via_http()
    {
        $plan1 = Plan::factory()->create();
        $plan2 = Plan::factory()->create();

        $sub = Subscription::factory()->create([
            'plan_id' => $plan1->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => '1',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        $response = $this->post("/justsubs/subscriptions/{$sub->id}/change-plan", [
            'plan_id' => $plan2->id,
        ]);

        $response->assertRedirect("/justsubs/subscriptions/{$sub->id}");

        $sub->refresh();
        $this->assertEquals($plan2->id, $sub->plan_id);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_cancel_subscription_gracefully_via_http()
    {
        $plan = Plan::factory()->create();
        $sub = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => '1',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        $response = $this->post("/justsubs/subscriptions/{$sub->id}/cancel", [
            'immediately' => 0,
        ]);

        $response->assertRedirect("/justsubs/subscriptions/{$sub->id}");

        $sub->refresh();
        $this->assertEquals(SubscriptionStatus::Active, $sub->status); // still active
        $this->assertNotNull($sub->cancelled_at); // but cancelled
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_terminate_subscription_immediately_via_http()
    {
        $plan = Plan::factory()->create();
        $sub = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => '1',
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        $response = $this->post("/justsubs/subscriptions/{$sub->id}/cancel", [
            'immediately' => 1,
        ]);

        $response->assertRedirect("/justsubs/subscriptions/{$sub->id}");

        $sub->refresh();
        $this->assertEquals(SubscriptionStatus::Cancelled, $sub->status);
        $this->assertNotNull($sub->cancelled_at);
        $this->assertTrue($sub->ends_at->isPast() || $sub->ends_at->isCurrentSecond());
    }
}
