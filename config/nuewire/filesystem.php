<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Component locale
    |--------------------------------------------------------------------------
    |
    | The package uses Indonesian by default, independent from the host
    | application's locale. Supported values can be selected in the component.
    */
    'locale' => env('NUEWIRE_FILESYSTEM_LOCALE', 'id'),
    'supported_locales' => ['id', 'en'],
    'remember_locale' => (bool) env('NUEWIRE_FILESYSTEM_REMEMBER_LOCALE', true),
    'locale_session_key' => 'nuewire.filesystem.locale',

    /*
    |--------------------------------------------------------------------------
    | Runtime disk names
    |--------------------------------------------------------------------------
    */
    'disk_prefix' => 'nuewire',

    /*
    | When enabled, the selected Nuewire disk becomes Laravel's default disk.
    */
    'set_as_default' => true,

    /*
    | The encrypted settings envelope is stored outside the public directory.
    */
    'settings_path' => storage_path('app/private/.nuewire/filesystem.json'),
    /*
    | Require an authenticated user before rendering or mutating the component.
    | Set NUEWIRE_FILESYSTEM_REQUIRE_AUTH=false only when the host route has its
    | own equivalent protection.
    */
    'authorization' => [
        'require_authenticated_user' => (bool) env('NUEWIRE_FILESYSTEM_REQUIRE_AUTH', true),
        'gate' => env('NUEWIRE_FILESYSTEM_GATE'),
        'guard' => env('NUEWIRE_FILESYSTEM_GUARD'),
    ],

    /*
    | Local storage uses Laravel's conventional public storage directory.
    */
    'local' => [
        'root' => storage_path('app/public'),
        'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/').'/storage',
        'visibility' => 'public',
    ],
];
