<?php

namespace Ridho\JustSubs\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Models\Subscription;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'subscriber_type' => 'App\Models\User',
            'subscriber_id' => (string) $this->faker->randomNumber(5),
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ];
    }
}
