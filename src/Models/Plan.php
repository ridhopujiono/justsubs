<?php

namespace Ridho\JustSubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Ridho\JustSubs\Database\Factories\PlanFactory;
use Ridho\JustSubs\Enums\IntervalUnit;

class Plan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'integer',
        'interval_count' => 'integer',
        'interval_unit' => IntervalUnit::class,
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function getTable()
    {
        return Config::get('justsubs.tables.plans', 'justsubs_plans');
    }

    protected static function newFactory()
    {
        return PlanFactory::new();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Calculate the end date for a period starting at a given date.
     */
    public function calculateNextPeriodEnd(?Carbon $start = null): Carbon
    {
        $start = $start ? $start->copy() : now();

        return match ($this->interval_unit) {
            IntervalUnit::Day => $start->addDays($this->interval_count),
            IntervalUnit::Week => $start->addWeeks($this->interval_count),
            IntervalUnit::Month => $start->addMonths($this->interval_count),
            IntervalUnit::Year => $start->addYears($this->interval_count),
        };
    }
}
