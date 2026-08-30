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

    /**
     * Renew a subscription.
     */
    public function renew(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            $plan = $subscription->plan;

            if ($subscription->active()) {
                // If active, extend from current ends_at to preserve remaining time
                $subscription->ends_at = $plan->calculateNextPeriodEnd($subscription->ends_at);
            } else {
                // If expired or cancelled, restart from now
                $subscription->status = SubscriptionStatus::Active;
                $subscription->starts_at = now();
                $subscription->ends_at = $plan->calculateNextPeriodEnd(now());
                $subscription->cancelled_at = null;
            }

            $subscription->save();

            \Ridho\JustSubs\Events\SubscriptionRenewed::dispatch($subscription);

            return $subscription;
        });
    }

    /**
     * Extend a subscription manually by a specific duration.
     */
    public function extend(
        Subscription $subscription, 
        int $count, 
        \Ridho\JustSubs\Enums\IntervalUnit $unit, 
        ?string $reason = null
    ): Subscription {
        if (!$subscription->ends_at) {
            throw new \InvalidArgumentException('Cannot extend a subscription that has not started yet.');
        }

        return DB::transaction(function () use ($subscription, $count, $unit, $reason) {
            $modifier = match ($unit) {
                \Ridho\JustSubs\Enums\IntervalUnit::Day => 'addDays',
                \Ridho\JustSubs\Enums\IntervalUnit::Week => 'addWeeks',
                \Ridho\JustSubs\Enums\IntervalUnit::Month => 'addMonths',
                \Ridho\JustSubs\Enums\IntervalUnit::Year => 'addYears',
            };
            
            $subscription->ends_at = $subscription->ends_at->$modifier($count);
            
            if ($reason) {
                $metadata = $subscription->metadata ?? [];
                $metadata['extensions'][] = [
                    'count' => $count,
                    'unit' => $unit->value,
                    'reason' => $reason,
                    'extended_at' => now()->toDateTimeString(),
                ];
                $subscription->metadata = $metadata;
            }

            $subscription->save();

            \Ridho\JustSubs\Events\SubscriptionExtended::dispatch($subscription);

            return $subscription;
        });
    }

    /**
     * Cancel a subscription.
     * 
     * @param Subscription $subscription
     * @param bool $immediately If true, revokes access instantly. If false, gracefully waits until ends_at.
     */
    public function cancel(Subscription $subscription, bool $immediately = false): Subscription
    {
        if ($subscription->cancelled()) {
            return $subscription;
        }

        return DB::transaction(function () use ($subscription, $immediately) {
            $subscription->cancelled_at = now();

            if ($immediately) {
                $subscription->status = SubscriptionStatus::Cancelled;
                $subscription->ends_at = now();
            }

            $subscription->save();

            \Ridho\JustSubs\Events\SubscriptionCancelled::dispatch($subscription);

            return $subscription;
        });
    }

    /**
     * Mark a subscription as expired if its period has ended.
     */
    public function markAsExpired(Subscription $subscription): Subscription
    {
        if ($subscription->ends_at->isPast() && $subscription->status !== SubscriptionStatus::Expired) {
            $subscription->status = SubscriptionStatus::Expired;
            $subscription->save();
            \Ridho\JustSubs\Events\SubscriptionExpired::dispatch($subscription);
        }

        return $subscription;
    }

    /**
     * Change the plan of an existing subscription without complex proration.
     */
    public function changePlan(Subscription $subscription, Plan $newPlan): Subscription
    {
        return DB::transaction(function () use ($subscription, $newPlan) {
            $oldPlanId = $subscription->plan_id;
            
            $subscription->plan_id = $newPlan->id;
            $subscription->status = SubscriptionStatus::Active;
            $subscription->starts_at = now();
            $subscription->ends_at = $newPlan->calculateNextPeriodEnd(now());
            $subscription->cancelled_at = null;
            $subscription->save();

            \Ridho\JustSubs\Events\SubscriptionPlanChanged::dispatch($subscription, $oldPlanId);

            return $subscription;
        });
    }
}
