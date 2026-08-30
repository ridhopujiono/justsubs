<?php

namespace Ridho\JustSubs\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ridho\JustSubs\Models\Invoice;

class InvoicePaid
{
    use Dispatchable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }
}
