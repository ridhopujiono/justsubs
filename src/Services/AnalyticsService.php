<?php

namespace Ridho\JustSubs\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Payment;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Enums\InvoiceStatus;
use Ridho\JustSubs\Enums\PaymentStatus;

class AnalyticsService
{
    /**
     * Get number of active subscriptions right now.
     */
    public function getActiveSubscriptionsCount(): int
    {
        return Subscription::where('status', SubscriptionStatus::Active->value)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->count();
    }

    /**
     * Get number of new subscriptions created in a specific period.
     */
    public function getNewSubscriptionsCount(Carbon $start, Carbon $end): int
    {
        return Subscription::whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * Get number of subscriptions expiring within a given number of days.
     */
    public function getExpiringSoonCount(int $days = 7): int
    {
        return Subscription::where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '>=', now())
            ->where('ends_at', '<=', now()->addDays($days))
            ->count();
    }

    /**
     * Get total revenue (sum of successful payments amount) in a specific period.
     */
    public function getRevenueReceived(Carbon $start, Carbon $end): int
    {
        return (int) Payment::where('status', PaymentStatus::Success->value)
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');
    }

    /**
     * Get total amount of outstanding unpaid invoices (not voided/draft/expired).
     */
    public function getOutstandingInvoiceAmount(): int
    {
        return (int) Invoice::where('status', InvoiceStatus::Unpaid->value)
            ->sum('amount');
    }

    /**
     * Get active subscriptions grouped by plan name.
     */
    public function getPlanDistribution(): array
    {
        $distribution = Subscription::join('justsubs_plans', 'justsubs_subscriptions.plan_id', '=', 'justsubs_plans.id')
            ->where('justsubs_subscriptions.status', SubscriptionStatus::Active->value)
            ->where('justsubs_subscriptions.starts_at', '<=', now())
            ->where('justsubs_subscriptions.ends_at', '>=', now())
            ->select('justsubs_plans.name', DB::raw('COUNT(justsubs_subscriptions.id) as count'))
            ->groupBy('justsubs_plans.name')
            ->pluck('count', 'name')
            ->toArray();

        return $distribution;
    }

    /**
     * Calculate a simplified Estimated Monthly Recurring Revenue (MRR).
     * Normalization logic:
     * - interval_unit 'day': price * 30
     * - interval_unit 'week': price * 4.33
     * - interval_unit 'month': price / interval_count
     * - interval_unit 'year': price / (12 * interval_count)
     */
    public function getEstimatedMRR(): int
    {
        $activeSubs = Subscription::with('plan')
            ->where('status', SubscriptionStatus::Active->value)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->get();

        $mrr = 0;

        foreach ($activeSubs as $sub) {
            $plan = $sub->plan;
            if (!$plan) continue;

            $price = $plan->price;
            $count = $plan->interval_count;
            $unit = $plan->interval_unit->value;

            $normalizedPrice = 0;

            if ($unit === 'day') {
                $normalizedPrice = ($price / $count) * 30;
            } elseif ($unit === 'week') {
                $normalizedPrice = ($price / $count) * 4.33;
            } elseif ($unit === 'month') {
                $normalizedPrice = $price / $count;
            } elseif ($unit === 'year') {
                $normalizedPrice = $price / ($count * 12);
            }

            $mrr += $normalizedPrice;
        }

        return (int) round($mrr);
    }
}
