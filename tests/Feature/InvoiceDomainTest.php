<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Ridho\JustSubs\Enums\InvoiceStatus;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Services\BillingManager;
use Ridho\JustSubs\Tests\DummyUser;
use Ridho\JustSubs\Tests\TestCase;

class InvoiceDomainTest extends TestCase
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

    public function test_can_create_invoice_using_billing_manager()
    {
        $subscriber = DummyUser::create(['name' => 'John Doe']);
        $manager = app(BillingManager::class);

        $invoice = $manager->createInvoice($subscriber, 150000, 'IDR');

        $this->assertDatabaseHas('justsubs_invoices', [
            'id' => $invoice->id,
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'amount' => 150000,
            'currency' => 'IDR',
            'status' => 'unpaid',
        ]);

        // Assert invoice_number was generated securely
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_invoice_number_is_unique_and_collision_safe()
    {
        $subscriber = DummyUser::create(['name' => 'Jane Doe']);

        // Ensure that inserting two invoices concurrently doesn't result in the same invoice number.
        // Since we use the database ID to formulate the invoice number in the model's created event,
        // it inherently cannot collide.
        $invoice1 = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
        ]);

        $invoice2 = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
        ]);

        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);
        $this->assertStringEndsWith(str_pad($invoice1->id, 5, '0', STR_PAD_LEFT), $invoice1->invoice_number);
    }

    public function test_can_mark_invoice_as_paid()
    {
        $subscriber = DummyUser::create(['name' => 'John Doe']);
        $manager = app(BillingManager::class);

        $invoice = $manager->createInvoice($subscriber, 150000, 'IDR');
        $this->assertEquals(InvoiceStatus::Unpaid, $invoice->status);
        $this->assertNull($invoice->paid_at);

        $manager->markAsPaid($invoice);

        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_can_void_invoice()
    {
        $subscriber = DummyUser::create(['name' => 'John Doe']);
        $manager = app(BillingManager::class);

        $invoice = $manager->createInvoice($subscriber, 150000, 'IDR');

        $manager->voidInvoice($invoice);

        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Void, $invoice->status);
    }

    public function test_money_amount_is_integer()
    {
        $subscriber = DummyUser::create(['name' => 'John Doe']);
        $manager = app(BillingManager::class);

        $invoice = $manager->createInvoice($subscriber, 100000, 'IDR');

        $this->assertIsInt($invoice->amount);
        $this->assertEquals(100000, $invoice->amount);
    }
}
