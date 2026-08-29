<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ridho\JustSubs\Concerns\HasSubscriptions;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Events\SubscriptionCreated;
use Ridho\JustSubs\Exceptions\AlreadySubscribedException;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Services\SubscriptionManager;
use Ridho\JustSubs\Tests\TestCase;
use Illuminate\Support\Str;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Schema::create('dummy_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('dummy_uuid_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });
    }

    public function test_integer_subscriber()
    {
        $user = DummyUser::create();
        $plan = Plan::factory()->create();

        $manager = new SubscriptionManager();
        $subscription = $manager->subscribe($user, $plan);

        $this->assertEquals((string)$user->id, $subscription->subscriber_id);
        $this->assertEquals(DummyUser::class, $subscription->subscriber_type);
        $this->assertTrue($user->subscribed());
    }

    public function test_uuid_subscriber()
    {
        $user = DummyUuidUser::create(['id' => Str::uuid()->toString()]);
        $plan = Plan::factory()->create();

        $manager = new SubscriptionManager();
        $subscription = $manager->subscribe($user, $plan);

        $this->assertEquals($user->id, $subscription->subscriber_id);
        $this->assertEquals(DummyUuidUser::class, $subscription->subscriber_type);
        $this->assertTrue($user->subscribed());
    }

    public function test_cannot_subscribe_if_already_active()
    {
        $user = DummyUser::create();
        $plan = Plan::factory()->create();

        $manager = new SubscriptionManager();
        $manager->subscribe($user, $plan);

        $this->expectException(AlreadySubscribedException::class);
        $manager->subscribe($user, $plan);
    }

    public function test_events_are_dispatched_and_active_detection()
    {
        Event::fake();

        $user = DummyUser::create();
        $plan = Plan::factory()->create(['slug' => 'pro']);

        $manager = new SubscriptionManager();
        $subscription = $manager->subscribe($user, $plan);

        Event::assertDispatched(SubscriptionCreated::class, function ($e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });

        $this->assertTrue($user->subscribed());
        $this->assertTrue($user->subscribedTo('pro'));
        $this->assertTrue($user->subscribedTo($plan));
        
        $this->assertNotNull($user->activeSubscription());
        $this->assertTrue($subscription->active());
    }

    public function test_future_subscription_is_not_active()
    {
        $this->travelTo(now());
        
        $user = DummyUser::create();
        $plan = Plan::factory()->create();
        
        $subscription = Subscription::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => (string)$user->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertFalse($user->subscribed());
        $this->assertFalse($subscription->active());
    }

    public function test_expired_subscription_is_not_active()
    {
        $this->travelTo(now());
        
        $user = DummyUser::create();
        $plan = Plan::factory()->create();
        
        $subscription = Subscription::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => (string)$user->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);

        $this->assertFalse($user->subscribed());
        $this->assertFalse($subscription->active());
    }
    
    public function test_cancelled_subscription_is_not_active_if_status_cancelled()
    {
        $user = DummyUser::create();
        $plan = Plan::factory()->create();
        
        $subscription = Subscription::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => (string)$user->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Cancelled,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(5),
            'cancelled_at' => now(),
        ]);

        $this->assertFalse($user->subscribed());
        $this->assertFalse($subscription->active());
        $this->assertTrue($subscription->cancelled());
    }
}

class DummyUser extends Model
{
    use HasSubscriptions;
    protected $guarded = [];
}

class DummyUuidUser extends Model
{
    use HasSubscriptions;
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';
}
