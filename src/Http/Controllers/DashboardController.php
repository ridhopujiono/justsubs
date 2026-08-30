<?php

namespace Ridho\JustSubs\Http\Controllers;

use Illuminate\Routing\Controller;
use Ridho\JustSubs\Services\AnalyticsService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analytics)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $metrics = [
            'active_subscriptions' => $analytics->getActiveSubscriptionsCount(),
            'new_subscriptions' => $analytics->getNewSubscriptionsCount($startOfMonth, $endOfMonth),
            'expiring_soon' => $analytics->getExpiringSoonCount(7),
            'revenue_this_month' => $analytics->getRevenueReceived($startOfMonth, $endOfMonth),
            'outstanding_invoices' => $analytics->getOutstandingInvoiceAmount(),
            'estimated_mrr' => $analytics->getEstimatedMRR(),
        ];

        $planDistribution = $analytics->getPlanDistribution();

        return view('justsubs::dashboard', compact('metrics', 'planDistribution'));
    }
}
