<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Ridho\JustSubs\Tests\TestCase;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Services\BillingManager;
use Ridho\JustSubs\Services\PaymentDrivers\ManualPaymentDriver;
use Ridho\JustSubs\Enums\InvoiceStatus;
use Ridho\JustSubs\Enums\PaymentStatus;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Exceptions\PaymentFailedException;
use Ridho\JustSubs\Tests\DummyUser;
use Ridho\JustSubs\Events\PaymentReceived;
use Ridho\JustSubs\Events\InvoicePaid;
use Ridho\JustSubs\Events\SubscriptionActivated;
use Ridho\JustSubs\Events\SubscriptionRenewed;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class PaymentDomainTest extends TestCase
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

    public function test_successful_manual_payment_activates_pending_subscription()
    {
        Event::fake([PaymentReceived::class, InvoicePaid::class, SubscriptionActivated::class]);

        $subscriber = DummyUser::create(['name' => 'John Doe']);
        $plan = Plan::factory()->create(['price' => 150000, 'currency' => 'IDR', 'interval_count' => 1, 'interval_unit' => 'month']);
        
        // Setup pending subscription
        $subscription = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Pending,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $billing = app(BillingManager::class);
        $invoice = $billing->createInvoice($subscriber, 150000, 'IDR', $subscription);

        $driver = new ManualPaymentDriver();
        $payment = $billing->processPayment($invoice, $driver, 150000, 'IDR');

        // Assert payment
        $this->assertEquals(PaymentStatus::Success, $payment->status);
        $this->assertEquals('manual', $payment->provider);
        $this->assertEquals(150000, $payment->amount);

        // Assert Invoice
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);

        // Assert Subscription Activated
        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertNotNull($subscription->ends_at);
        
        // Assert Events
        Event::assertDispatched(PaymentReceived::class);
        Event::assertDispatched(InvoicePaid::class);
        Event::assertDispatched(SubscriptionActivated::class);
    }

    public function test_successful_manual_payment_renews_active_subscription()
    {
        Event::fake([SubscriptionRenewed::class]);

        $subscriber = DummyUser::create();
        $plan = Plan::factory()->create(['interval_count' => 1, 'interval_unit' => 'month']);
        
        $originalEndsAt = now()->addDays(5);
        $subscription = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(25),
            'ends_at' => clone $originalEndsAt,
        ]);

        $billing = app(BillingManager::class);
        $invoice = $billing->createInvoice($subscriber, $plan->price, $plan->currency, $subscription);

        $driver = new ManualPaymentDriver();
        $billing->processPayment($invoice, $driver, $plan->price, $plan->currency);

        $subscription->refresh();
        $this->assertTrue($subscription->ends_at->gt($originalEndsAt));
        Event::assertDispatched(SubscriptionRenewed::class);
    }

    public function test_cannot_pay_wrong_amount()
    {
        $subscriber = DummyUser::create();
        $billing = app(BillingManager::class);
        $invoice = $billing->createInvoice($subscriber, 150000, 'IDR');

        $driver = new ManualPaymentDriver();

        $this->expectException(PaymentFailedException::class);
        $this->expectExceptionMessage('Payment amount does not match invoice amount.');
        
        $billing->processPayment($invoice, $driver, 140000, 'IDR');
    }

    public function test_cannot_pay_wrong_currency()
    {
        $subscriber = DummyUser::create();
        $billing = app(BillingManager::class);
        $invoice = $billing->createInvoice($subscriber, 150000, 'IDR');

        $driver = new ManualPaymentDriver();

        $this->expectException(PaymentFailedException::class);
        $this->expectExceptionMessage('Payment currency does not match invoice currency.');
        
        $billing->processPayment($invoice, $driver, 150000, 'USD');
    }

    public function test_duplicate_payment_fails()
    {
        $subscriber = DummyUser::create();
        $billing = app(BillingManager::class);
        $invoice = $billing->createInvoice($subscriber, 150000, 'IDR');

        $driver = new ManualPaymentDriver();
        $billing->processPayment($invoice, $driver, 150000, 'IDR');

        $this->expectException(PaymentFailedException::class);
        $this->expectExceptionMessage('Invoice is already paid.');
        
        // Second attempt
        $billing->processPayment($invoice, $driver, 150000, 'IDR');
    }

    public function test_transaction_rollback_on_failure()
    {
        $subscriber = DummyUser::create();
        $billing = app(BillingManager::class);
        $invoice = $billing->createInvoice($subscriber, 150000, 'IDR');

        $driver = new ManualPaymentDriver();

        try {
            $billing->processPayment($invoice, $driver, 999, 'IDR');
        } catch (\Exception $e) {
            // caught
        }

        $this->assertDatabaseMissing('justsubs_payments', [
            'invoice_id' => $invoice->id,
        ]);
        
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Unpaid, $invoice->status);
    }
}
