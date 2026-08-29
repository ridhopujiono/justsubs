<?php

namespace Ridho\JustSubs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ridho\JustSubs\Models\Subscription;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Services\SubscriptionManager;
use Ridho\JustSubs\Enums\IntervalUnit;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    protected SubscriptionManager $manager;

    public function __construct(SubscriptionManager $manager)
    {
        $this->manager = $manager;
    }

    public function index(Request $request)
    {
        $query = Subscription::with(['plan', 'subscriber'])->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('subscriber_id')) {
            $query->where('subscriber_id', $request->subscriber_id);
        }

        $subscriptions = $query->paginate(20)->withQueryString();
        $plans = Plan::where('is_active', true)->get();

        return view('justsubs::subscriptions.index', compact('subscriptions', 'plans'));
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['plan', 'subscriber']);
        $plans = Plan::where('is_active', true)->where('id', '!=', $subscription->plan_id)->get();

        return view('justsubs::subscriptions.show', compact('subscription', 'plans'));
    }

    public function renew(Subscription $subscription)
    {
        $this->manager->renew($subscription);

        return redirect()->route('justsubs.subscriptions.show', $subscription)
            ->with('success', 'Subscription renewed successfully.');
    }

    public function extend(Request $request, Subscription $subscription)
    {
        $request->validate([
            'count' => 'required|integer|min:1',
            'unit' => ['required', Rule::enum(IntervalUnit::class)],
            'reason' => 'nullable|string|max:255',
        ]);

        $unit = IntervalUnit::from($request->unit);

        $this->manager->extend($subscription, $request->count, $unit, $request->reason);

        return redirect()->route('justsubs.subscriptions.show', $subscription)
            ->with('success', 'Subscription extended successfully.');
    }

    public function changePlan(Request $request, Subscription $subscription)
    {
        $request->validate([
            'plan_id' => 'required|exists:justsubs_plans,id',
        ]);

        $newPlan = Plan::findOrFail($request->plan_id);

        $this->manager->changePlan($subscription, $newPlan);

        return redirect()->route('justsubs.subscriptions.show', $subscription)
            ->with('success', 'Subscription plan changed successfully.');
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        $request->validate([
            'immediately' => 'boolean',
        ]);

        $immediately = $request->boolean('immediately', false);

        $this->manager->cancel($subscription, $immediately);

        return redirect()->route('justsubs.subscriptions.show', $subscription)
            ->with('success', $immediately ? 'Subscription terminated immediately.' : 'Subscription cancelled at period end.');
    }
}
