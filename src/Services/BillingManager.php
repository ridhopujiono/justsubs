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

    /**
     * Mark an invoice as paid.
     * Note: This does NOT automatically activate subscriptions.
     * Subscription activation should be handled by a higher-level workflow or event listener.
     */
    public function markAsPaid(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->status = InvoiceStatus::Paid;
            $invoice->paid_at = now();
            $invoice->save();

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
