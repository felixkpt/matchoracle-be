<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nested Routes config
    |--------------------------------------------------------------------------
    |
    | This option controls the nested routes behavior.
    |
    */
    'folder' => 'nested-routes',
    'prefix' => 'api',
    'middleWares' => ['api', 'nesteroutes.auth'],
    'permissions' => [
        'ignored_folders' => env('permissions_ignored_folders', [
            'auth',
            'client',
        ]),
    ],

    'rename_root_folders' => [
        'admin' => 'dashboard',
    ],
    'expanded_root_folders' => [
        'dashboard',
    ],
    'guestRoleId' => null,

    'defaultPublicRoutes' => [
        'auth/role-permissions/roles/get-user-roles-and-direct-permissions',
        'auth/role-permissions/roles/view/{id}/get-role-menu',
        'auth/role-permissions/roles/view/{id}/get-role-route-permissions',
        'file-repo/*',
    ]
];
