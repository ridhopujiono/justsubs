<?php

namespace Ridho\JustSubs;

use Closure;

class JustSubs
{
    /**
     * The callback that should be used to authenticate dashboard users.
     *
     * @var \Closure|null
     */
    public static ?Closure $authUsing = null;

    /**
     * Determine if the given request can access the dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
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
     * @param  \Closure  $callback
     * @return static
     */
    public static function auth(Closure $callback)
    {
        static::$authUsing = $callback;

        return new static;
    }

    /**
     * The callback that should be used to resolve subscriber display names.
     *
     * @var \Closure|null
     */
    public static ?Closure $subscriberNameResolver = null;

    /**
     * Set the callback that should be used to resolve subscriber names.
     *
     * @param  \Closure  $callback
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
     * @return string
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
}
