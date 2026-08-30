<?php

namespace Ridho\JustSubs\Services\PaymentDrivers;

use Illuminate\Support\Facades\DB;
use Ridho\JustSubs\Contracts\PaymentDriver;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Payment;
use Ridho\JustSubs\Enums\PaymentStatus;
use Ridho\JustSubs\Enums\InvoiceStatus;
use Ridho\JustSubs\Exceptions\PaymentFailedException;

class ManualPaymentDriver implements PaymentDriver
{
    public function getProviderName(): string
    {
        return 'manual';
    }

    public function process(Invoice $invoice, int $amount, string $currency, array $metadata = []): Payment
    {
        return DB::transaction(function () use ($invoice, $amount, $currency, $metadata) {
            // Lock invoice to prevent race conditions during payment processing
            $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status === InvoiceStatus::Paid) {
                throw PaymentFailedException::alreadyPaid();
            }

            if ($invoice->status !== InvoiceStatus::Unpaid) {
                throw PaymentFailedException::invalidInvoiceStatus();
            }

            if ($invoice->amount !== $amount) {
                throw PaymentFailedException::invalidAmount();
            }

            if (strtoupper($invoice->currency) !== strtoupper($currency)) {
                throw PaymentFailedException::invalidCurrency();
            }

            $payment = $invoice->payments()->create([
                'provider' => $this->getProviderName(),
                'provider_reference' => $metadata['reference'] ?? null,
                'amount' => $amount,
                'currency' => $currency,
                'status' => PaymentStatus::Success,
                'paid_at' => now(),
                'metadata' => empty($metadata) ? null : $metadata,
            ]);

            return $payment;
        });
    }
}
