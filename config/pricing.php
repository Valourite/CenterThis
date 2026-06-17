<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pricing rule engine
    |--------------------------------------------------------------------------
    | Pricing rules are configured from the pricing_rules table. Admins define
    | each rule's scope, conditions, effect type, value, priority, and active
    | state without adding PHP classes or updating this config file.
    */

    /*
    |--------------------------------------------------------------------------
    | Inclusive day count
    |--------------------------------------------------------------------------
    | true:  collect Mon, return Fri = 5 rental days (both ends billed)
    | false: collect Mon, return Fri = 4 rental days
    | A booking always bills at least one day.
    */
    'inclusive_days' => env('INCLUDE_DAYS', false),
];
