<?php

namespace Ridho\JustSubs\Contracts;

use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Payment;

interface PaymentDriver
{
    /**
     * Get the driver's identifier (e.g. 'manual', 'midtrans', 'stripe').
     */
    public function getProviderName(): string;

    /**
     * Process a payment for a specific invoice.
     * Must return the created Payment record if successful.
     * Should throw exceptions for failures like wrong amount, wrong currency, or duplicate processing.
     */
    public function process(Invoice $invoice, int $amount, string $currency, array $metadata = []): Payment;
}
