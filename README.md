# JustSubs

**JustSubs** is a solid, self-hosted subscription and billing infrastructure package for Laravel applications. It abstracts away the complexity of managing plans, polymorphic subscriptions, invoices, and payment processing while providing a built-in, standalone Tailwind CSS dashboard.

## Features

- 🏗️ **Domain Modeling**: Complete abstraction for Plans, Subscriptions, Invoices, and Payments.
- 👥 **Polymorphic Subscribers**: Bind subscriptions to `User`, `Team`, `Company`, or any Eloquent model.
- 🎨 **Built-in Dashboard**: A ready-to-use Tailwind CSS control panel. No extra frontend compilation required for your host app.
- 🔌 **Agnostic Payments**: Extensible Payment Driver system. Start with manual payments and extend it later (e.g. Midtrans, Stripe).
- 📊 **Analytics Overview**: Automatically calculates active subscriptions, revenue, and normalized Estimated MRR (Monthly Recurring Revenue).
- 🛡️ **Secure & Scalable**: Race-condition safe with pessimistic locking, highly optimized queries avoiding N+1 memory exhaustion, and CSRF-protected dashboard endpoints.

## Requirements

- PHP 8.2+
- Laravel 11.x
- Relational Database (MySQL, PostgreSQL, SQLite)

## Installation

You can install the package via composer:

```bash
composer require ridho/justsubs
```

Publish the package configuration, migrations, and dashboard assets:

```bash
php artisan justsubs:install
```

Run the database migrations:

```bash
php artisan migrate
```

## Configuration

After running the install command, you can modify the configuration file located at `config/justsubs.php`. 
Here you can change the table names (to prevent collisions with packages like Laravel Cashier) and customize the dashboard route prefix (defaults to `/justsubs`).

## Dashboard Authorization

By default, the dashboard is only accessible in the `local` environment. To authorize users in production, register an authorization callback within the `boot` method of your `App\Providers\AppServiceProvider`:

```php
use Ridho\JustSubs\JustSubs;

public function boot()
{
    JustSubs::auth(function ($request) {
        // Example: Only allow users with an 'admin' role
        return $request->user() && $request->user()->is_admin;
    });
}
```

Since JustSubs uses Polymorphic subscribers, you can also define how to display the subscriber's name in the dashboard:

```php
JustSubs::resolveSubscriberNameUsing(function ($subscriber) {
    return $subscriber->company_name ?? $subscriber->name;
});
```

## Adding `HasSubscriptions`

Add the `HasSubscriptions` trait to the Eloquent model that will be acting as the subscriber (e.g., your `User` or `Team` model).

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Ridho\JustSubs\Concerns\HasSubscriptions;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

## Creating Plans

Plans can be created easily via the built-in Dashboard or programmatically using Eloquent.

```php
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Enums\IntervalUnit;

$plan = Plan::create([
    'name' => 'Pro Monthly',
    'slug' => 'pro-monthly',
    'description' => 'Full access on a monthly basis.',
    'price' => 150000, // Stored in smallest unit (e.g., IDR or Cents)
    'currency' => 'IDR',
    'interval_count' => 1,
    'interval_unit' => IntervalUnit::Month,
    'is_active' => true,
]);
```

## Creating Subscriptions

To subscribe a user to a plan, use the `SubscriptionManager` service. Note that overlapping active subscriptions are blocked by default.

```php
use Ridho\JustSubs\Services\SubscriptionManager;

$manager = app(SubscriptionManager::class);

// Activates immediately and sets the ends_at date
$subscription = $manager->subscribe($user, $plan);
```

To check subscription status on your subscriber model:

```php
if ($user->subscribed()) {
    // Has an active subscription
}

if ($user->subscribedTo('pro-monthly')) {
    // Specifically subscribed to the 'pro-monthly' plan slug
}
```

## Renewing

Subscriptions can be renewed via the dashboard or code. If the subscription is currently active, the renewal preserves the remaining time by adding the interval to the current `ends_at`. If it has expired, it restarts from `now()`.

```php
$manager->renew($subscription);
```

You can also manually extend a subscription (e.g. compensating a user for downtime):

```php
use Ridho\JustSubs\Enums\IntervalUnit;

// Extends the subscription by 5 days
$manager->extend($subscription, 5, IntervalUnit::Day, 'Apology for server downtime');
```

## Cancelling

You can cancel a subscription gracefully (access remains until the period ends) or immediately.

```php
// Graceful cancellation (status stays Active until ends_at)
$manager->cancel($subscription, immediately: false);

// Immediate termination
$manager->cancel($subscription, immediately: true);
```

## Invoices

To issue a bill for a new or existing subscription, use the `BillingManager`. This creates a collision-safe invoice number (e.g., `INV-202608-00001`).

```php
use Ridho\JustSubs\Services\BillingManager;

$billing = app(BillingManager::class);

$invoice = $billing->createInvoice(
    subscriber: $user, 
    amount: 150000, 
    currency: 'IDR', 
    subscription: $subscription // Link it to activate/renew upon payment
);
```

## Manual Payments

If a user pays via bank transfer or outside a gateway, you can mark the invoice as paid. This process automatically activates or renews the linked subscription safely inside a database transaction.

```php
use Ridho\JustSubs\Services\PaymentDrivers\ManualPaymentDriver;

$driver = new ManualPaymentDriver();
$billing->processPayment($invoice, $driver, $amount = 150000, 'IDR', [
    'reference' => 'Bank Transfer TXN-12345'
]);
```

*This can also be done visually by clicking "Mark as Paid (Manual)" on the Invoice Dashboard.*

## Dashboard

Navigate to your application's `/justsubs` URI to access the dashboard.
It includes:
- **Analytics Overview:** Active count, Outstanding revenue, MRR, Plan distribution.
- **Plans:** Full CRUD for plan management.
- **Subscriptions:** Table viewer, filtering, and manual operation interfaces (Renew, Extend, Cancel, Change Plan).
- **Invoices:** Billing records and manual payment processing.
- **Payments:** Immutable ledger of successful transactions.

## Events

JustSubs dispatches eloquent events you can listen to in order to send emails or run webhook synchronizations:

- `Ridho\JustSubs\Events\PaymentReceived`
- `Ridho\JustSubs\Events\InvoicePaid`
- `Ridho\JustSubs\Events\SubscriptionCreated`
- `Ridho\JustSubs\Events\SubscriptionActivated`
- `Ridho\JustSubs\Events\SubscriptionRenewed`
- `Ridho\JustSubs\Events\SubscriptionExtended`
- `Ridho\JustSubs\Events\SubscriptionPlanChanged`
- `Ridho\JustSubs\Events\SubscriptionCancelled`
- `Ridho\JustSubs\Events\SubscriptionExpired`

## Payment Driver Extension

JustSubs operates on a flexible `PaymentDriver` contract. You can integrate Midtrans, Stripe, or Xendit by extending the registry without modifying core code.

1. Implement `\Ridho\JustSubs\Contracts\PaymentDriver`.
2. Register it in your `AppServiceProvider`:

```php
use Ridho\JustSubs\JustSubs;

JustSubs::extendPaymentDriver('midtrans', function () {
    return new \App\Services\MidtransDriver();
});
```

## Testing

```bash
composer test
```
The test suite spans across unit tests and HTTP feature tests, enforcing behavior with SQLite memory databases. All operations check for Race Conditions, Type Casting, and Duplicate Subscriptions.

## Security

If you discover any security related issues, please create an issue or contact the author directly. Do not expose vulnerability details openly on public trackers.
Built-in securities:
- **CSRF Protection:** Verified on all state-changing dashboard routes.
- **Idempotency Check:** Duplicate payments to an already-paid invoice are instantly rejected with `PaymentFailedException`.
- **Pessimistic Locking:** Parallel payment callbacks lock the DB row (`lockForUpdate()`) to prevent race conditions.

## Upgrade Notes

No prior versions exist. The first release candidate is `v0.1.0`. Wait for the 1.0.0 battle-tested release before massive production adoption.

## License

The MIT License (MIT).
