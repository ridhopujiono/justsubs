<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Ridho\JustSubs\Enums\IntervalUnit;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Tests\TestCase;
use ValueError;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_plan()
    {
        $plan = Plan::factory()->create([
            'name' => 'Pro Plan',
            'slug' => 'pro',
            'price' => 99000,
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => IntervalUnit::Month,
        ]);

        $this->assertDatabaseHas('justsubs_plans', [
            'slug' => 'pro',
            'price' => 99000,
        ]);

        $this->assertIsInt($plan->price);
        $this->assertInstanceOf(IntervalUnit::class, $plan->interval_unit);
        $this->assertTrue($plan->is_active);
    }

    public function test_it_casts_features_to_array()
    {
        $plan = Plan::factory()->create([
            'features' => ['A', 'B'],
        ]);

        $this->assertIsArray($plan->features);
        $this->assertCount(2, $plan->features);
    }

    public function test_invalid_interval_throws_error()
    {
        $this->expectException(ValueError::class);

        Plan::factory()->create([
            'interval_unit' => 'decade',
        ]);
    }

    public function test_calculates_next_period_end_for_days()
    {
        $plan = Plan::factory()->make([
            'interval_count' => 30,
            'interval_unit' => IntervalUnit::Day,
        ]);

        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = $plan->calculateNextPeriodEnd($start);

        $this->assertEquals('2024-01-31 10:00:00', $end->toDateTimeString());
    }

    public function test_calculates_next_period_end_for_weeks()
    {
        $plan = Plan::factory()->make([
            'interval_count' => 2,
            'interval_unit' => IntervalUnit::Week,
        ]);

        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = $plan->calculateNextPeriodEnd($start);

        $this->assertEquals('2024-01-15 10:00:00', $end->toDateTimeString());
    }

    public function test_calculates_next_period_end_for_months()
    {
        $plan = Plan::factory()->make([
            'interval_count' => 1,
            'interval_unit' => IntervalUnit::Month,
        ]);

        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = $plan->calculateNextPeriodEnd($start);

        $this->assertEquals('2024-02-01 10:00:00', $end->toDateTimeString());
    }

    public function test_calculates_next_period_end_for_years()
    {
        $plan = Plan::factory()->make([
            'interval_count' => 1,
            'interval_unit' => IntervalUnit::Year,
        ]);

        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = $plan->calculateNextPeriodEnd($start);

        $this->assertEquals('2025-01-01 10:00:00', $end->toDateTimeString());
    }
}
