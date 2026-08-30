<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Ridho\JustSubs\Enums\PaymentStatus;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Payment;
use Ridho\JustSubs\Tests\DummyUser;
use Ridho\JustSubs\Tests\TestCase;

class PaymentDashboardTest extends TestCase
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
    public function test_can_list_payments()
    {
        $subscriber = DummyUser::create(['name' => 'Bob']);
        $invoice = Invoice::factory()->create([
            'subscriber_type' => DummyUser::class,
            'subscriber_id' => $subscriber->id,
            'amount' => 50000,
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'provider' => 'manual',
            'provider_reference' => 'TXN-999',
            'amount' => 50000,
            'currency' => 'IDR',
            'status' => PaymentStatus::Success,
            'paid_at' => now(),
        ]);

        $response = $this->get('/justsubs/payments');
        $response->assertStatus(200);
        $response->assertSee('#'.$payment->id);
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('TXN-999');
        $response->assertSee('50,000 IDR');
    }
}
