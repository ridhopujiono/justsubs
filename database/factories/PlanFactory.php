<?php

namespace Ridho\JustSubs\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Ridho\JustSubs\Enums\IntervalUnit;
use Ridho\JustSubs\Models\Plan;

class PlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(10000, 1000000),
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => IntervalUnit::Month,
            'features' => ['feature1', 'feature2'],
            'is_active' => true,
        ];
    }
}
