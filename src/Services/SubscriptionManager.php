<?php

namespace Ridho\JustSubs\Services;

use Illuminate\Support\Facades\DB;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Events\SubscriptionCreated;
use Ridho\JustSubs\Exceptions\AlreadySubscribedException;

class SubscriptionManager
{
    /**
     * Subscribe a subscriber to a given plan.
     */
    public function subscribe($subscriber, Plan $plan): Subscription
    {
        if (method_exists($subscriber, 'subscribed') && $subscriber->subscribed()) {
            throw AlreadySubscribedException::forSubscriber($subscriber);
        }

        return DB::transaction(function () use ($subscriber, $plan) {
            $startsAt = now();
            $endsAt = $plan->calculateNextPeriodEnd($startsAt);

            $subscription = $subscriber->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Active,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            SubscriptionCreated::dispatch($subscription);

            return $subscription;
        });
    }
}
