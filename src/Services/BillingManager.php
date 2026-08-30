<?php

namespace Ridho\JustSubs\Services;

use Illuminate\Support\Facades\DB;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Enums\InvoiceStatus;

class BillingManager
{
    /**
     * Create an invoice for a subscriber.
     */
    public function createInvoice($subscriber, int $amount, string $currency = 'IDR', ?Subscription $subscription = null, array $metadata = []): Invoice
    {
        return DB::transaction(function () use ($subscriber, $amount, $currency, $subscription, $metadata) {
            $invoice = Invoice::create([
                'subscriber_type' => get_class($subscriber),
                'subscriber_id' => $subscriber->getKey(),
                'subscription_id' => $subscription?->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => InvoiceStatus::Unpaid,
                'due_at' => now()->addDays(7),
                'metadata' => empty($metadata) ? null : $metadata,
            ]);

            return $invoice;
        });
    }

    public function processPayment(Invoice $invoice, \Ridho\JustSubs\Contracts\PaymentDriver $driver, int $amount, string $currency, array $metadata = []): \Ridho\JustSubs\Models\Payment
    {
        return DB::transaction(function () use ($invoice, $driver, $amount, $currency, $metadata) {
            $payment = $driver->process($invoice, $amount, $currency, $metadata);

            \Ridho\JustSubs\Events\PaymentReceived::dispatch($payment);

            if ($payment->status === \Ridho\JustSubs\Enums\PaymentStatus::Success) {
                $this->markAsPaid($invoice);
                
                if ($invoice->subscription_id) {
                    $subscription = $invoice->subscription;
                    
                    if ($subscription->status === \Ridho\JustSubs\Enums\SubscriptionStatus::Pending) {
                        $subscription->status = \Ridho\JustSubs\Enums\SubscriptionStatus::Active;
                        $subscription->starts_at = now();
                        $subscription->ends_at = $subscription->plan->calculateNextPeriodEnd(now());
                        $subscription->save();
                        
                        \Ridho\JustSubs\Events\SubscriptionActivated::dispatch($subscription);
                    } else {
                        app(\Ridho\JustSubs\Services\SubscriptionManager::class)->renew($subscription);
                    }
                }
            }

            return $payment;
        });
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            if ($invoice->status === InvoiceStatus::Paid) {
                return $invoice;
            }

            $invoice->status = InvoiceStatus::Paid;
            $invoice->paid_at = now();
            $invoice->save();

            \Ridho\JustSubs\Events\InvoicePaid::dispatch($invoice);

            return $invoice;
        });
    }

    /**
     * Void an invoice.
     */
    public function voidInvoice(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->status = InvoiceStatus::Void;
            $invoice->save();

            return $invoice;
        });
    }
}
