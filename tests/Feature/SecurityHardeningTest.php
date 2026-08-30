<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ridho\JustSubs\Tests\TestCase;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Services\SubscriptionManager;
use Ridho\JustSubs\Enums\IntervalUnit;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Tests\DummyUser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SecurityHardeningTest extends TestCase
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
    }

    public function test_cannot_extend_pending_subscription_with_null_ends_at()
    {
        $subscriber = DummyUser::create();
        $plan = Plan::factory()->create();

        $subscription = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Pending,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $manager = app(SubscriptionManager::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot extend a subscription that has not started yet.');

        $manager->extend($subscription, 1, IntervalUnit::Month);
    }
}
