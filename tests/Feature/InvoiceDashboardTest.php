<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Ridho\JustSubs\Enums\InvoiceStatus;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Tests\DummyUser;
use Ridho\JustSubs\Tests\TestCase;

class InvoiceDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironmentForTesting($app)
    {
        $app['config']->set('justsubs.route.middleware', ['web']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('dash_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        JustSubs::auth(function () {
            return true;
        });
    }

    protected function tearDown(): void
    {
        JustSubs::$authUsing = null;
        parent::tearDown();
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_list_and_filter_invoices()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);

        $invoice1 = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => InvoiceStatus::Unpaid,
            'amount' => 1000,
        ]);

        $invoice2 = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => InvoiceStatus::Paid,
            'amount' => 2000,
        ]);

        $response = $this->get('/justsubs/invoices');
        $response->assertStatus(200);
        $response->assertSee($invoice1->invoice_number);
        $response->assertSee($invoice2->invoice_number);

        $response = $this->get('/justsubs/invoices?status=unpaid');
        $response->assertSee($invoice1->invoice_number);
        $response->assertDontSee($invoice2->invoice_number);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_show_invoice_details()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);
        $invoice = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => InvoiceStatus::Unpaid,
            'amount' => 150000,
        ]);

        $response = $this->get('/justsubs/invoices/'.$invoice->id);
        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('150,000 IDR');
        $response->assertSee('Mark as Paid (Manual)');
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_mark_invoice_as_paid_manually()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);
        $invoice = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => InvoiceStatus::Unpaid,
            'amount' => 150000,
        ]);

        $response = $this->post('/justsubs/invoices/'.$invoice->id.'/mark-paid', [
            'reference' => 'Bank Transfer 123',
        ]);

        $response->assertRedirect('/justsubs/invoices/'.$invoice->id);
        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);

        $this->assertDatabaseHas('justsubs_payments', [
            'invoice_id' => $invoice->id,
            'provider' => 'manual',
            'provider_reference' => 'Bank Transfer 123',
            'status' => 'success',
        ]);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_cannot_mark_already_paid_invoice_as_paid()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);
        $invoice = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => InvoiceStatus::Paid,
            'amount' => 150000,
        ]);

        $response = $this->post('/justsubs/invoices/'.$invoice->id.'/mark-paid', [
            'reference' => 'Bank Transfer 123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error'); // Caught from PaymentFailedException

        $this->assertDatabaseCount('justsubs_payments', 0);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_can_void_unpaid_invoice()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);
        $invoice = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => InvoiceStatus::Unpaid,
        ]);

        $response = $this->post('/justsubs/invoices/'.$invoice->id.'/void');
        $response->assertRedirect('/justsubs/invoices/'.$invoice->id);

        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Void, $invoice->status);
    }

    #[DefineEnvironment('defineEnvironmentForTesting')]
    public function test_cannot_void_paid_invoice()
    {
        $subscriber = DummyUser::create(['name' => 'Alice']);
        $invoice = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'status' => InvoiceStatus::Paid,
        ]);

        $response = $this->post('/justsubs/invoices/'.$invoice->id.'/void');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot void an already paid invoice.');

        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
    }
}
