<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ridho\JustSubs\Tests\TestCase;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Payment;
use Ridho\JustSubs\Services\AnalyticsService;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Enums\InvoiceStatus;
use Ridho\JustSubs\Enums\PaymentStatus;
use Ridho\JustSubs\Tests\DummyUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AnalyticsServiceTest extends TestCase
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

    public function test_active_subscriptions_count()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);
        
        // Active
        Subscription::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(10),
        ]);

        // Active but expired (should not count if we strict check ends_at)
        Subscription::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
        ]);

        // Pending
        Subscription::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Pending,
        ]);

        $analytics = app(AnalyticsService::class);
        $this->assertEquals(1, $analytics->getActiveSubscriptionsCount());
    }

    public function test_revenue_received()
    {
        $start = Carbon::create(2023, 1, 1);
        $end = Carbon::create(2023, 1, 31, 23, 59, 59);

        Carbon::setTestNow(Carbon::create(2023, 1, 15));

        $invoice = Invoice::factory()->create();

        // Inside period - success
        Payment::create([
            'invoice_id' => $invoice->id,
            'provider' => 'manual',
            'amount' => 50000,
            'status' => PaymentStatus::Success,
            'paid_at' => Carbon::create(2023, 1, 15),
        ]);

        // Inside period - failed
        Payment::create([
            'invoice_id' => $invoice->id,
            'provider' => 'manual',
            'amount' => 100000,
            'status' => PaymentStatus::Failed,
            'paid_at' => Carbon::create(2023, 1, 16),
        ]);

        // Outside period
        Payment::create([
            'invoice_id' => $invoice->id,
            'provider' => 'manual',
            'amount' => 200000,
            'status' => PaymentStatus::Success,
            'paid_at' => Carbon::create(2023, 2, 5),
        ]);

        $analytics = app(AnalyticsService::class);
        $this->assertEquals(50000, $analytics->getRevenueReceived($start, $end));
        
        Carbon::setTestNow(); // reset
    }

    public function test_outstanding_invoices()
    {
        Invoice::factory()->create(['amount' => 10000, 'status' => InvoiceStatus::Unpaid]);
        Invoice::factory()->create(['amount' => 20000, 'status' => InvoiceStatus::Unpaid]);
        Invoice::factory()->create(['amount' => 30000, 'status' => InvoiceStatus::Paid]);
        Invoice::factory()->create(['amount' => 40000, 'status' => InvoiceStatus::Void]);

        $analytics = app(AnalyticsService::class);
        $this->assertEquals(30000, $analytics->getOutstandingInvoiceAmount());
    }

    public function test_plan_distribution()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);
        
        $planA = Plan::factory()->create(['name' => 'Plan A']);
        $planB = Plan::factory()->create(['name' => 'Plan B']);

        // 2 Active on A
        Subscription::factory()->count(2)->create([
            'plan_id' => $planA->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(10),
        ]);

        // 1 Active on B
        Subscription::factory()->count(1)->create([
            'plan_id' => $planB->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(10),
        ]);

        // 1 Pending on B (should not count)
        Subscription::factory()->count(1)->create([
            'plan_id' => $planB->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Pending,
        ]);

        $analytics = app(AnalyticsService::class);
        $distribution = $analytics->getPlanDistribution();

        $this->assertCount(2, $distribution);
        $this->assertEquals(2, $distribution['Plan A']);
        $this->assertEquals(1, $distribution['Plan B']);
    }

    public function test_estimated_mrr_normalization()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);
        
        $monthlyPlan = Plan::factory()->create(['price' => 100000, 'interval_unit' => 'month', 'interval_count' => 1]); // MRR = 100000
        $yearlyPlan = Plan::factory()->create(['price' => 1200000, 'interval_unit' => 'year', 'interval_count' => 1]); // MRR = 100000
        $weeklyPlan = Plan::factory()->create(['price' => 20000, 'interval_unit' => 'week', 'interval_count' => 1]); // MRR = 20000 * 4.33 = 86600

        // Create 1 active sub for each
        foreach ([$monthlyPlan, $yearlyPlan, $weeklyPlan] as $plan) {
            Subscription::factory()->create([
                'plan_id' => $plan->id,
                'subscriber_type' => DummyUser::class,
                'subscriber_id' => $subscriber->id,
                'status' => SubscriptionStatus::Active,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(30),
            ]);
        }

        $analytics = app(AnalyticsService::class);
        $mrr = $analytics->getEstimatedMRR();

        // 100000 + 100000 + 86600 = 286600
        $this->assertEquals(286600, $mrr);
    }
}
