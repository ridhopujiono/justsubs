<?php

namespace Ridho\JustSubs\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Enums\InvoiceStatus;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'subscriber_type' => 'App\Models\User',
            'subscriber_id' => $this->faker->uuid(),
            'amount' => $this->faker->numberBetween(10000, 500000),
            'currency' => 'IDR',
            'status' => InvoiceStatus::Unpaid,
            'due_at' => now()->addDays(7),
        ];
    }
}
