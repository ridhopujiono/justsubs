<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ridho\JustSubs\Concerns\HasSubscriptions;
use Ridho\JustSubs\Enums\IntervalUnit;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Events\SubscriptionCancelled;
use Ridho\JustSubs\Events\SubscriptionExpired;
use Ridho\JustSubs\Events\SubscriptionExtended;
use Ridho\JustSubs\Events\SubscriptionPlanChanged;
use Ridho\JustSubs\Events\SubscriptionRenewed;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Services\SubscriptionManager;
use Ridho\JustSubs\Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Schema::create('lifecycle_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function test_renew_active_subscription_adds_to_ends_at()
    {
        Event::fake();
        
        $this->travelTo(now());
        
        $plan = Plan::factory()->create([
            'interval_count' => 1,
            'interval_unit' => IntervalUnit::Month,
        ]);
        
        $subscription = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addDays(15), // Still active for 15 days
        ]);

        $manager = new SubscriptionManager();
        $manager->renew($subscription);

        $subscription->refresh();
        
        // Next period end for 1 month from original ends_at
        $this->assertEquals(now()->addDays(15)->addMonths(1)->toDateTimeString(), $subscription->ends_at->toDateTimeString());
        Event::assertDispatched(SubscriptionRenewed::class);
    }

    public function test_renew_expired_subscription_restarts_from_now()
    {
        $this->travelTo(now());
        
        $plan = Plan::factory()->create([
            'interval_count' => 1,
            'interval_unit' => IntervalUnit::Month,
        ]);
        
        $subscription = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Expired,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonths(1),
        ]);

        $manager = new SubscriptionManager();
        $manager->renew($subscription);

        $subscription->refresh();
        
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
        $this->assertEquals(now()->toDateTimeString(), $subscription->starts_at->toDateTimeString());
        $this->assertEquals(now()->addMonths(1)->toDateTimeString(), $subscription->ends_at->toDateTimeString());
    }

    public function test_extend_adds_duration_and_metadata()
    {
        Event::fake();
        $this->travelTo(now());
        
        $subscription = Subscription::factory()->create([
            'ends_at' => now()->addDays(10),
        ]);

        $manager = new SubscriptionManager();
        $manager->extend($subscription, 7, IntervalUnit::Day, 'Manual compensation');

        $subscription->refresh();
        
        $this->assertEquals(now()->addDays(17)->toDateTimeString(), $subscription->ends_at->toDateTimeString());
        
        $this->assertIsArray($subscription->metadata);
        $this->assertEquals('Manual compensation', $subscription->metadata['extensions'][0]['reason']);
        Event::assertDispatched(SubscriptionExtended::class);
    }

    public function test_graceful_cancel()
    {
        Event::fake();
        $this->travelTo(now());
        
        $endsAt = now()->addDays(10);
        $subscription = Subscription::factory()->create([
            'status' => SubscriptionStatus::Active,
            'ends_at' => $endsAt,
        ]);

        $manager = new SubscriptionManager();
        $manager->cancel($subscription, false); // graceful

        $subscription->refresh();
        
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
        $this->assertEquals($endsAt->toDateTimeString(), $subscription->ends_at->toDateTimeString());
        $this->assertTrue($subscription->active()); // Still active during grace period
        $this->assertTrue($subscription->cancelled());
        
        Event::assertDispatched(SubscriptionCancelled::class);
    }

    public function test_immediate_cancel()
    {
        $this->travelTo(now());
        
        $subscription = Subscription::factory()->create([
            'status' => SubscriptionStatus::Active,
            'ends_at' => now()->addDays(10),
        ]);

        $manager = new SubscriptionManager();
        $manager->cancel($subscription, true); // immediate

        $subscription->refresh();
        
        $this->assertEquals(SubscriptionStatus::Cancelled, $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
        $this->assertEquals(now()->toDateTimeString(), $subscription->ends_at->toDateTimeString());
        $this->assertFalse($subscription->active()); // Instantly inactive
    }

    public function test_mark_as_expired()
    {
        Event::fake();
        $this->travelTo(now());
        
        $subscription = Subscription::factory()->create([
            'status' => SubscriptionStatus::Active,
            'ends_at' => now()->subDays(1), // already past ends_at
        ]);

        $manager = new SubscriptionManager();
        $manager->markAsExpired($subscription);

        $subscription->refresh();
        
        $this->assertEquals(SubscriptionStatus::Expired, $subscription->status);
        Event::assertDispatched(SubscriptionExpired::class);
    }

    public function test_change_plan_without_proration()
    {
        Event::fake();
        $this->travelTo(now());
        
        $oldPlan = Plan::factory()->create();
        $newPlan = Plan::factory()->create([
            'interval_count' => 1,
            'interval_unit' => IntervalUnit::Year,
        ]);
        
        $subscription = Subscription::factory()->create([
            'plan_id' => $oldPlan->id,
            'ends_at' => now()->addDays(10),
        ]);

        $manager = new SubscriptionManager();
        $manager->changePlan($subscription, $newPlan);

        $subscription->refresh();
        
        $this->assertEquals($newPlan->id, $subscription->plan_id);
        $this->assertEquals(now()->toDateTimeString(), $subscription->starts_at->toDateTimeString());
        $this->assertEquals(now()->addYears(1)->toDateTimeString(), $subscription->ends_at->toDateTimeString());
        
        Event::assertDispatched(SubscriptionPlanChanged::class, function ($e) use ($oldPlan) {
            return $e->oldPlanId === $oldPlan->id;
        });
    }
}

class LifecycleUser extends Model
{
    use HasSubscriptions;
    protected $guarded = [];
}
