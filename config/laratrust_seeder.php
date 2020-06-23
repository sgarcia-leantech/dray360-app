<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => false,

    'roles_structure' => [
        'superadmin' => [
            'rules-editor' => 'e',
            'orders' => 'e,v,c',
            'tms' => 's',
            'users' => 'v,c,e,r',
            'roles' => 'u',
            'system-status' => 'f',
            'time-in-status' => 'v',
        ],
        'customer-admin' => [
            'orders' => 'e,v,c',
            'tms' => 's',
            'users' => 'v,c,e,r',
            'roles' => 'u',
        ],
        'customer-user' => [
            'orders' => 'v',
        ],
    ],

    'permissions_map' => [
        'c' => 'create',
        'v' => 'view',
        's' => 'submit',
        'u' => 'update',
        'e' => 'edit',
        'r' => 'remove',
        'f' => 'filter',
    ]
];
