<?php

namespace Ridho\JustSubs\Http\Controllers;

use Illuminate\Routing\Controller;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Http\Requests\SavePlanRequest;
use Ridho\JustSubs\Enums\SubscriptionStatus;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount(['subscriptions' => function ($query) {
            $query->where('status', SubscriptionStatus::Active->value)
                  ->where('starts_at', '<=', now())
                  ->where('ends_at', '>=', now());
        }])->orderBy('id', 'desc')->paginate(20);

        return view('justsubs::plans.index', compact('plans'));
    }

    public function create()
    {
        $plan = new Plan([
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => \Ridho\JustSubs\Enums\IntervalUnit::Month,
            'is_active' => true,
        ]);

        return view('justsubs::plans.create', compact('plan'));
    }

    public function store(SavePlanRequest $request)
    {
        $data = $request->validated();
        $data['features'] = $this->parseFeatures($data['features'] ?? '');

        Plan::create($data);

        return redirect()->route('justsubs.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('justsubs::plans.edit', compact('plan'));
    }

    public function update(SavePlanRequest $request, Plan $plan)
    {
        $data = $request->validated();
        $data['features'] = $this->parseFeatures($data['features'] ?? '');

        $plan->update($data);

        return redirect()->route('justsubs.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    protected function parseFeatures(?string $features): array
    {
        if (empty(trim($features))) {
            return [];
        }
        
        $lines = explode("\n", str_replace("\r\n", "\n", $features));
        return array_values(array_filter(array_map('trim', $lines)));
    }
}
