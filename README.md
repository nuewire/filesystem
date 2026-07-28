# Nuewire Filesystem

Filesystem settings for Laravel and Livewire.

## Install

```bash
composer require nuewire/filesystem
php artisan optimize:clear
```

## Component

```blade
<livewire:nuewire::filesystem />
```

With `nuewire/platform`, the page appears automatically under admin settings.

## Storage

Settings are encrypted at:

```text
storage/app/private/.nuewire/filesystem.json
```

Available disks:

```text
nuewire
nuewire-local
nuewire-s3
nuewire-bunnycdn
```

`nuewire` always points to the active provider.

```php
Storage::disk('nuewire')->put('files/example.txt', $contents);
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
NUEWIRE_FILESYSTEM_GATE=manage-filesystem-settings
```

## Publish

```bash
php artisan vendor:publish --tag=nuewire-filesystem-config
php artisan vendor:publish --tag=nuewire-filesystem-views
php artisan vendor:publish --tag=nuewire-filesystem-translations
```

Config path:

```text
config/nuewire/filesystem.php
```

Restart queue or Octane workers after changing providers.
