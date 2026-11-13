<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Permission matrix by role
    |--------------------------------------------------------------------------
    |
    | Each ability maps to a list of roles that are granted it by default.
    | Roles: owner, caretaker, tenant (renter), admin
    |
    | Caretaker permissions can be overridden per-caretaker via the
    | caretaker_permissions table (owner-granted grants).
    */

    'abilities' => [

        // Payments
        'payments.record'   => ['owner', 'caretaker', 'tenant'],
        'payments.reverse'  => ['owner'],
        'payments.confirm'  => ['tenant'],
        'payments.view'     => ['owner', 'caretaker', 'tenant'],

        // Bills
        'bills.generate'    => ['owner'],
        'bills.view'        => ['owner', 'caretaker', 'tenant'],

        // Renters
        'renters.create'    => ['owner'],
        'renters.terminate' => ['owner'],
        'renters.view'      => ['owner', 'caretaker', 'tenant'],

        // Caretakers
        'caretakers.manage' => ['owner'],
        'caretakers.view'   => ['owner', 'caretaker'],

        // Properties
        'properties.manage' => ['owner'],
        'properties.view'   => ['owner', 'caretaker'],

        // Reports
        'reports.view'      => ['owner', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role defaults
    |--------------------------------------------------------------------------
    |
    | Default permission set per role when no caretaker_permissions override
    | exists. Used as the baseline for caretaker permission resolution.
    */

    'role_defaults' => [

        'owner' => [
            'payments.record', 'payments.reverse', 'payments.confirm', 'payments.view',
            'bills.generate', 'bills.view',
            'renters.create', 'renters.terminate', 'renters.view',
            'caretakers.manage', 'caretakers.view',
            'properties.manage', 'properties.view',
            'reports.view',
        ],

        'caretaker' => [
            'payments.record',
            'payments.view',
            'bills.view',
            'renters.view',
            'caretakers.view',
            'properties.view',
        ],

        'tenant' => [
            'payments.confirm',
            'payments.view',
            'bills.view',
            'renters.view',
        ],

        'admin' => [
            '*', // super_admin has all abilities
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role hierarchy
    |--------------------------------------------------------------------------
    |
    | Higher roles inherit all abilities of lower roles unless explicitly
    | overridden.
    */

    'hierarchy' => [
        'admin' => ['owner', 'caretaker', 'tenant'],
        'owner' => ['caretaker', 'tenant'],
        'caretaker' => [],
        'tenant' => [],
    ],

];
