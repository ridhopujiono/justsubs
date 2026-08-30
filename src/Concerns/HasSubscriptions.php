<?php

namespace Ridho\JustSubs\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;

trait HasSubscriptions
{
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriber');
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', SubscriptionStatus::Active->value)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest('id')
            ->first();
    }

    public function subscribed(): bool
    {
        return $this->activeSubscription() !== null;
    }

    public function subscribedTo(string|Plan $plan): bool
    {
        $subscription = $this->activeSubscription();

        if (! $subscription) {
            return false;
        }

        if ($plan instanceof Plan) {
            return $subscription->plan_id === $plan->id;
        }

        return $subscription->plan->slug === $plan;
    }
}
