# JustSubs

A solid, self-hosted subscription infrastructure package for Laravel applications.

JustSubs provides everything you need to manage Plans, Subscriptions, Invoices, and Payments with a built-in beautiful dashboard.

## Requirements
- PHP 8.2+
- Laravel 11.x

## Features
- Complete domain modeling for Subscriptions, Plans, Invoices, and Payments.
- Polymorphic subscribers (bind subscriptions to Users, Teams, or Companies).
- A standalone, built-in management Dashboard (Tailwind CSS, no extra frontend build required).
- Agnostic Payment Provider system (starts with a manual driver, extensible to Midtrans, Stripe, Xendit, etc).
- Analytics overview built-in.

## Installation

Install via composer (assuming this is a local package or published):
```bash
composer require ridho/justsubs
```

Run the installation command to publish configuration and assets:
```bash
php artisan justsubs:install
```

Run migrations:
```bash
php artisan migrate
```

## Public API Reference

### 1. The `HasSubscriptions` Trait
Add this trait to your subscriber model (e.g. `App\Models\User`).

```php
use Ridho\JustSubs\Concerns\HasSubscriptions;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

This gives you access to the following methods:
- `$user->subscriptions()`: MorphMany relation.
- `$user->activeSubscription()`: Returns the currently active `Subscription` model, or null.
- `$user->subscribed()`: Returns `bool`. True if the user has any active subscription.
- `$user->subscribedTo('pro')`: Returns `bool`. True if the user is subscribed to the plan with slug `pro`.

### 2. Authorization
By default, the dashboard at `/justsubs` is only accessible in the `local` environment.
To allow specific users in production, register an authorization callback in your `AppServiceProvider` boot method:

```php
use Ridho\JustSubs\JustSubs;

JustSubs::auth(function ($request) {
    return $request->user() && $request->user()->is_admin;
});
```

### 3. Displaying Subscriber Names
Because JustSubs supports Polymorphic subscribers, it needs a way to display their names safely on the Dashboard.
You can configure how it resolves a name:

```php
JustSubs::resolveSubscriberNameUsing(function ($subscriber) {
    return $subscriber->company_name ?? $subscriber->name;
});
```

### 4. Extending Payment Drivers
JustSubs uses a `manual` payment driver by default. You can build your own (e.g. for Midtrans) by implementing `\Ridho\JustSubs\Contracts\PaymentDriver`.

```php
use Ridho\JustSubs\JustSubs;

JustSubs::extendPaymentDriver('midtrans', function () {
    return new App\Services\MidtransDriver();
});
```
When processing payments, JustSubs will resolve your driver using `JustSubs::getPaymentDriver('midtrans')`.

### 5. Events
You can listen to these events in your application:
- `Ridho\JustSubs\Events\PaymentReceived`
- `Ridho\JustSubs\Events\InvoicePaid`
- `Ridho\JustSubs\Events\SubscriptionActivated`
- `Ridho\JustSubs\Events\SubscriptionRenewed`
- `Ridho\JustSubs\Events\SubscriptionCancelled`
- `Ridho\JustSubs\Events\SubscriptionExpired`
