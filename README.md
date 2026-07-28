# Btekno Filesystem

Filesystem settings for Laravel and Livewire.

## Install

```bash
composer require btekno/filesystem
php artisan optimize:clear
```

## Component

```blade
<livewire:btekno::filesystem />
```

With `btekno/platform`, the page appears automatically under admin settings.

## Storage

Settings are encrypted at:

```text
storage/app/private/.btekno/filesystem.json
```

Available disks:

```text
btekno
btekno-local
btekno-s3
btekno-bunnycdn
```

`btekno` always points to the active provider.

```php
Storage::disk('btekno')->put('files/example.txt', $contents);
```

The selected disk can become Laravel's default. Calls with an explicit disk such as `Storage::disk('public')` are not changed.

## Base directory

Set a relative directory such as:

```text
my-app/media
```

A write to `avatars/user.jpg` is stored below `my-app/media/avatars/user.jpg`.

## Local storage

```bash
php artisan storage:link
```

## Access

The component requires authentication by default.

```env
BTEKNO_FILESYSTEM_GATE=manage-filesystem-settings
```

## Publish

```bash
php artisan vendor:publish --tag=btekno-filesystem-config
php artisan vendor:publish --tag=btekno-filesystem-views
php artisan vendor:publish --tag=btekno-filesystem-translations
```

Config path:

```text
config/btekno/filesystem.php
```

Restart queue or Octane workers after changing providers.
