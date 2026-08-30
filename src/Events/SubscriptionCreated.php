<?php

namespace Ridho\JustSubs\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ridho\JustSubs\Models\Subscription;

class SubscriptionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Subscription $subscription) {}
}
