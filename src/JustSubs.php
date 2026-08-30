<?php

namespace Ridho\JustSubs;

use Closure;
use Illuminate\Http\Request;
use Ridho\JustSubs\Contracts\PaymentDriver;
use Ridho\JustSubs\Services\PaymentDrivers\ManualPaymentDriver;

class JustSubs
{
    /**
     * The callback that should be used to authenticate dashboard users.
     */
    public static ?Closure $authUsing = null;

    /**
     * Determine if the given request can access the dashboard.
     *
     * @param  Request  $request
     * @return bool
     */
    public static function check($request)
    {
        return (static::$authUsing ?: function () {
            return app()->environment('local');
        })($request);
    }

    /**
     * Set the callback that should be used to authenticate dashboard users.
     *
     * @return static
     */
    public static function auth(Closure $callback)
    {
        static::$authUsing = $callback;

        return new static;
    }

    /**
     * The callback that should be used to resolve subscriber display names.
     */
    public static ?Closure $subscriberNameResolver = null;

    /**
     * Set the callback that should be used to resolve subscriber names.
     *
     * @return static
     */
    public static function resolveSubscriberNameUsing(Closure $callback)
    {
        static::$subscriberNameResolver = $callback;

        return new static;
    }

    /**
     * Get the display name for a subscriber.
     *
     * @param  mixed  $subscriber
     */
    public static function getSubscriberName($subscriber): string
    {
        if (static::$subscriberNameResolver) {
            return call_user_func(static::$subscriberNameResolver, $subscriber);
        }

        if (! $subscriber) {
            return 'Unknown';
        }

        return $subscriber->name ?? $subscriber->email ?? $subscriber->id ?? 'Unknown';
    }

    /**
     * Registered payment drivers.
     */
    protected static array $paymentDrivers = [];

    /**
     * Register a custom payment driver.
     *
     * @return void
     */
    public static function extendPaymentDriver(string $name, Closure $resolver)
    {
        static::$paymentDrivers[$name] = $resolver;
    }

    /**
     * Resolve a registered payment driver.
     *
     * @return PaymentDriver
     *
     * @throws \Exception
     */
    public static function getPaymentDriver(string $name = 'manual')
    {
        if ($name === 'manual' && ! isset(static::$paymentDrivers['manual'])) {
            return new ManualPaymentDriver;
        }

        if (! isset(static::$paymentDrivers[$name])) {
            throw new \Exception("Payment driver [{$name}] is not registered.");
        }

        return call_user_func(static::$paymentDrivers[$name]);
    }
}
