<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Ridho\JustSubs\Contracts\PaymentDriver;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Payment;
use Ridho\JustSubs\Services\PaymentDrivers\ManualPaymentDriver;
use Ridho\JustSubs\Tests\TestCase;

class ExtensibilityTest extends TestCase
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

    protected function tearDown(): void
    {
        // Reset static properties
        $reflection = new \ReflectionClass(JustSubs::class);
        $property = $reflection->getProperty('paymentDrivers');
        $property->setAccessible(true);
        $property->setValue([]);

        parent::tearDown();
    }

    public function test_can_resolve_default_manual_payment_driver()
    {
        $driver = JustSubs::getPaymentDriver('manual');
        $this->assertInstanceOf(ManualPaymentDriver::class, $driver);
    }

    public function test_can_extend_and_resolve_custom_payment_driver()
    {
        $customDriver = new class implements PaymentDriver
        {
            public function getProviderName(): string
            {
                return 'midtrans';
            }

            public function process(Invoice $invoice, int $amount, string $currency, array $metadata = []): Payment
            {
                return new Payment;
            }
        };

        JustSubs::extendPaymentDriver('midtrans', function () use ($customDriver) {
            return $customDriver;
        });

        $resolved = JustSubs::getPaymentDriver('midtrans');

        $this->assertSame($customDriver, $resolved);
        $this->assertEquals('midtrans', $resolved->getProviderName());
    }

    public function test_throws_exception_if_driver_not_found()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment driver [unknown] is not registered.');

        JustSubs::getPaymentDriver('unknown');
    }
}
