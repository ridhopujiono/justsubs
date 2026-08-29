<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | This option configures the names of the database tables used by JustSubs.
    | You are free to change these values if you already have tables with
    | these names and wish to avoid collision with JustSubs package.
    |
    */

    'tables' => [
        'plans' => 'justsubs_plans',
        'subscriptions' => 'justsubs_subscriptions',
        'invoices' => 'justsubs_invoices',
        'payments' => 'justsubs_payments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes Configuration
    |--------------------------------------------------------------------------
    |
    | These options configure the route prefix and middleware for the
    | JustSubs dashboard. You can customize the path and protect the
    | dashboard by adding your own middleware here.
    |
    */

    'route' => [
        'prefix' => 'justsubs',

        'middleware' => [
            'web',
            'auth',
        ],
    ],

];
