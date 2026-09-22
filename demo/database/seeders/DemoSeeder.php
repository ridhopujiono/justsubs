<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Services\BillingManager;
use Ridho\JustSubs\Services\PaymentDrivers\ManualPaymentDriver;
use Ridho\JustSubs\Services\SubscriptionManager;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Demo User
        User::create([
            'name' => 'JustSubs Admin',
            'email' => 'admin@justsubs.test',
            'password' => bcrypt('password'),
        ]);

        // 2. Realistic Names for Users & Companies
        $userNames = [
            'Andi Pratama', 'Budi Santoso', 'Citra Permata', 'Dewi Lestari', 'Fajar Nugroho',
            'Gilang Ramadhan', 'Hana Maharani', 'Indra Setiawan', 'Joko Susilo', 'Kartika Putri',
            'Lukman Hakim', 'Maya Wulandari', 'Nina Safitri', 'Okan Kusuma', 'Putri Diana',
        ];

        $companyNames = [
            'Nusantara Digital', 'Arunika Labs', 'Bintang Teknologi', 'Kreasi Data Indonesia', 'Langit Software',
            'Bumi Inovasi', 'Samudra Solusi', 'Mega Corpora', 'Sentosa Jaya', 'Maju Bersama',
        ];

        // 3. Plans
        $plans = [
            'starter' => Plan::create([
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => '3 Projects, Basic Reports, Email Support',
                'price' => 29000,
                'currency' => 'IDR',
                'interval_count' => 1,
                'interval_unit' => 'month',
                'is_active' => true,
            ]),
            'basic' => Plan::create([
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => '10 Projects, Reports, Export, Email Support',
                'price' => 49000,
                'currency' => 'IDR',
                'interval_count' => 1,
                'interval_unit' => 'month',
                'is_active' => true,
            ]),
            'pro' => Plan::create([
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Unlimited Projects, Advanced Reports, Export, Priority Support',
                'price' => 99000,
                'currency' => 'IDR',
                'interval_count' => 1,
                'interval_unit' => 'month',
                'is_active' => true,
            ]),
            'business' => Plan::create([
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Multiple Teams, Advanced Analytics, Priority Support, Custom Branding',
                'price' => 999000,
                'currency' => 'IDR',
                'interval_count' => 1,
                'interval_unit' => 'year',
                'is_active' => true,
            ]),
        ];

        $planKeys = array_keys($plans);

        // 4. Subscriptions, Invoices, Payments
        $subscriptionManager = app(SubscriptionManager::class);
        $billingManager = app(BillingManager::class);

        // Distributions: 15 active, 5 expiring soon, 5 expired, 3 cancelled
        $scenarios = array_merge(
            array_fill(0, 15, 'active'),
            array_fill(0, 5, 'expiring_soon'),
            array_fill(0, 5, 'expired'),
            array_fill(0, 3, 'cancelled')
        );

        $now = Carbon::now();

        foreach ($scenarios as $index => $state) {
            $isCompany = $index % 3 === 0;

            if ($isCompany) {
                $subscriber = Company::create([
                    'name' => $companyNames[$index % count($companyNames)].' '.$index,
                    'email' => 'company'.$index.'@example.com',
                ]);
            } else {
                $subscriber = User::create([
                    'name' => $userNames[$index % count($userNames)].' '.$index,
                    'email' => 'user'.$index.'@example.com',
                    'password' => bcrypt('password'),
                ]);
            }

            $planKey = $planKeys[$index % count($planKeys)];
            $plan = $plans[$planKey];

            // Setup dates
            $startsAt = $now->copy();
            $endsAt = $now->copy();
            $cancelledAt = null;

            switch ($state) {
                case 'active':
                    $startsAt = $now->copy()->subDays(10);
                    $endsAt = clone $startsAt;
                    $endsAt = $plan->interval_unit === 'year'
                        ? $endsAt->addYears($plan->interval_count)
                        : $endsAt->addMonths($plan->interval_count);
                    $subStatus = 'active';
                    $invoiceStatus = 'paid';
                    break;
                case 'expiring_soon':
                    $endsAt = $now->copy()->addDays(3);
                    $startsAt = clone $endsAt;
                    $startsAt = $plan->interval_unit === 'year'
                        ? $startsAt->subYears($plan->interval_count)
                        : $startsAt->subMonths($plan->interval_count);
                    $subStatus = 'active';
                    $invoiceStatus = 'paid';
                    break;
                case 'expired':
                    $endsAt = $now->copy()->subDays(10);
                    $startsAt = clone $endsAt;
                    $startsAt = $plan->interval_unit === 'year'
                        ? $startsAt->subYears($plan->interval_count)
                        : $startsAt->subMonths($plan->interval_count);
                    $subStatus = 'expired';
                    $invoiceStatus = 'paid';
                    break;
                case 'cancelled':
                    $startsAt = $now->copy()->subDays(20);
                    $endsAt = clone $startsAt;
                    $endsAt = $plan->interval_unit === 'year'
                        ? $endsAt->addYears($plan->interval_count)
                        : $endsAt->addMonths($plan->interval_count);
                    $cancelledAt = $now->copy()->subDays(2);
                    $subStatus = 'cancelled';
                    $invoiceStatus = 'paid';
                    break;
            }

            // Create via API to dispatch events properly, then forcefully update timestamps
            $subscription = $subscriptionManager->subscribe($subscriber, $plan);

            $subscription->update([
                'status' => $subStatus,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'cancelled_at' => $cancelledAt,
                'created_at' => $startsAt,
            ]);

            // Create invoice
            $invoice = $billingManager->createInvoice($subscriber, $plan->price, $plan->currency, $subscription);

            $invoice->update([
                'created_at' => $startsAt,
                'due_at' => clone $startsAt,
            ]);

            if ($invoiceStatus === 'paid') {
                $billingManager->processPayment(
                    $invoice,
                    app(ManualPaymentDriver::class),
                    $plan->price,
                    $plan->currency,
                    [
                        'provider_reference' => 'MANUAL-'.str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                        'paid_at' => clone $startsAt->copy()->addHours(1),
                    ]
                );

                // Update payment date manually for realistic timeline
                $payment = $invoice->payments()->latest()->first();
                if ($payment) {
                    $payment->update([
                        'created_at' => $startsAt->copy()->addHours(1),
                        'paid_at' => $startsAt->copy()->addHours(1),
                    ]);
                }
            }
        }

        // Unpaid Invoices
        $subscriber1 = User::create(['name' => 'Andi', 'email' => 'andi@example.com', 'password' => bcrypt('password')]);
        $invoice = $billingManager->createInvoice($subscriber1, 29000, 'IDR');
        $invoice->update(['due_at' => $now->copy()->addDays(7)]);

        // Void Invoice
        $subscriber2 = User::create(['name' => 'Budi', 'email' => 'budi@example.com', 'password' => bcrypt('password')]);
        $invoice = $billingManager->createInvoice($subscriber2, 49000, 'IDR');
        $billingManager->voidInvoice($invoice);
    }
}
