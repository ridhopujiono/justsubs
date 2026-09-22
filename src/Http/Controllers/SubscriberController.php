<?php

namespace Ridho\JustSubs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Models\Subscription;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        // Get unique subscribers who have a subscription record
        $query = Subscription::select('subscriber_type', 'subscriber_id')
            ->groupBy('subscriber_type', 'subscriber_id');

        $subscribers = $query->paginate(20);

        $subscribers->getCollection()->transform(function ($item) {
            // Fetch a full subscription model to get the polymorphic subscriber
            $subscription = Subscription::where('subscriber_type', $item->subscriber_type)
                ->where('subscriber_id', $item->subscriber_id)
                ->latest()
                ->first();

            if ($subscription && $subscription->subscriber) {
                $model = $subscription->subscriber;
                $item->model = $model;
                $item->resolved_name = JustSubs::getSubscriberName($model);

                if (method_exists($model, 'activeSubscription')) {
                    $item->active_subscription = $model->activeSubscription();
                } else {
                    // Fallback if trait is missing
                    $item->active_subscription = Subscription::where('subscriber_type', $item->subscriber_type)
                        ->where('subscriber_id', $item->subscriber_id)
                        ->where('status', SubscriptionStatus::Active->value)
                        ->where('starts_at', '<=', now())
                        ->where('ends_at', '>=', now())
                        ->latest('id')
                        ->first();
                }
            } else {
                $item->model = null;
                $item->resolved_name = 'Unknown / Deleted';
                $item->active_subscription = null;
            }

            $item->total_invoices = Invoice::where('subscriber_type', $item->subscriber_type)
                ->where('subscriber_id', $item->subscriber_id)
                ->count();

            return $item;
        });

        return view('justsubs::subscribers.index', compact('subscribers'));
    }
}
