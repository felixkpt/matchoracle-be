<?php

return [
  'folder' => env('NESTED_ROUTES_FOLDER', 'nested-routes'),
  'permissions' => [
      'ignored_folders' => [
          0 => 'auth',
          1 => 'client',
      ],
  ],
  'rename_main_folders' => [
      'admin' => env('ADMIN_FOLDER_NAME', 'dashboard'),
  ],
];

