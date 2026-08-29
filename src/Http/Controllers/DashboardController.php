<?php

namespace Ridho\JustSubs\Http\Controllers;

use Illuminate\Routing\Controller;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Enums\SubscriptionStatus;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPlans = Plan::count();
        
        $activeSubscriptions = Subscription::where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '>=', now())
            ->where('starts_at', '<=', now())
            ->count();
            
        $expiringSoon = Subscription::where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '>=', now())
            ->where('ends_at', '<=', now()->addDays(7))
            ->count();

        return view('justsubs::dashboard', [
            'total_plans' => $totalPlans,
            'active_subscriptions' => $activeSubscriptions,
            'expiring_soon' => $expiringSoon,
        ]);
    }
}
