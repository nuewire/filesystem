# Btekno Filesystem

[![Latest Version on Packagist](https://img.shields.io/packagist/v/btekno/filesystem.svg?style=flat-square)](https://packagist.org/packages/btekno/filesystem)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/btekno/filesystem.svg?style=flat-square)](https://packagist.org/packages/btekno/filesystem)

Select Language: [Bahasa Indonesia](README.md) | **[English]**

---

A reusable Laravel package for selecting Local, Amazon S3, or Bunny CDN storage through one Livewire component.

The package does not use a database. It stores an encrypted JSON envelope at:

```text
storage/app/private/.btekno/filesystem.json
```

The encrypted payload uses Laravel's application encrypter and `APP_KEY`.

## Requirements

- PHP 8.2 or later
- Laravel 11, 12, or 13
- Livewire 3 or 4

The package installs Laravel's S3 Flysystem adapter, so Amazon S3 and Bunny's S3-compatible API work without an additional filesystem package.

## Install

After publishing the repository to Packagist or another Composer repository:

```bash
composer require btekno/filesystem
```

Laravel package discovery registers the service provider automatically.

Render the settings UI inside an authenticated administration page:

```blade
<livewire:btekno::filesystem />
```

The host layout must already load Livewire's assets.

The package registers the component with Livewire 4's namespace API and falls back to Livewire 3's class alias API automatically.

## Languages

The component includes Indonesian and English translations. Indonesian is the package default, even when the host application's locale is different.

Use the default Indonesian interface:

```blade
<livewire:btekno::filesystem />
```

Force English for one component instance:

```blade
<livewire:btekno::filesystem locale="en" />
```

Set the package-wide default in `.env`:

```env
BTEKNO_FILESYSTEM_LOCALE=id
BTEKNO_FILESYSTEM_REMEMBER_LOCALE=true
```

The language selector stores the selected locale in the session when session middleware is available. Supported locale codes are configured in `config/btekno/filesystem.php`.

After installing or updating the package, clear cached discovery and views:

```bash
composer dump-autoload
php artisan optimize:clear
```

## Registered disks

The service provider registers stable disk names:

```text
btekno-local
btekno-s3
btekno-bunnycdn
```

It also registers `btekno` as an alias for the currently selected provider.

By default, the selected stable disk becomes Laravel's `filesystems.default` value. The current values are available through:

```php
config('btekno.filesystem.active_provider'); // local, s3, or bunnycdn

config('btekno.filesystem.active_disk');      // btekno-local, btekno-s3, or btekno-bunnycdn

config('btekno.filesystem.active_directory'); // empty string or configured base directory
```

For applications that may change providers, store both the path and the stable disk name with each uploaded file:

```php
$disk = config('btekno.filesystem.active_disk');
$path = request()->file('photo')->store('photos', $disk);

// Persist both $disk and $path.
```

Reading an older file remains explicit:

```php
$url = Storage::disk($model->disk)->url($model->path);
```

A generic package cannot migrate existing files or infer which provider owns a path that was stored without a disk name.

## Base directory

Each provider has an optional base directory field. Use a relative value such as:

```text
my-app/media
```

The package applies that value as the disk root. Application code can continue using ordinary relative paths:

```php
Storage::disk('btekno')->put('avatars/user-1.jpg', $contents);
```

With `my-app/media` configured, the physical object or file is stored at:

```text
my-app/media/avatars/user-1.jpg
```

For Local storage, the full path becomes `storage/app/public/my-app/media/avatars/user-1.jpg`. For S3 and Bunny CDN, the key is created below the selected bucket or Storage Zone directory. Generated URLs include the same prefix.

The field accepts letters, numbers, dots, underscores, hyphens, and forward slashes. Leading and trailing slashes are removed. Repeated slashes are collapsed. The package rejects `.` and `..` path segments. Leaving the field empty keeps the disk at its original root.

Changing the base directory affects new filesystem operations. Existing records still contain their original relative paths. Keep the old disk and directory information when an application must continue serving files from a previous location.

## Local storage

Local storage uses `storage/app/public`. Run this once in the host application:

```bash
php artisan storage:link
```

## Bunny CDN

Create a Bunny Storage Zone with S3 compatibility enabled. In the component, enter:

- Storage Zone name
- Storage Zone password
- Bunny S3 endpoint, such as `https://sg-s3.storage.bunnycdn.com`
- Pull Zone URL, such as `https://example.b-cdn.net`
- Optional Storage Zone directory, such as `my-app/media`

The package uses path-style S3 addressing. The Storage Zone name becomes both the access key and bucket name.

## Security

The component requires an authenticated user by default. Place it on an admin-only route.

To use a Laravel Gate, set:

```env
BTEKNO_FILESYSTEM_GATE=manage-filesystem-settings
```

A non-default authentication guard can be selected without publishing the package config:

```env
BTEKNO_FILESYSTEM_GUARD=admin
```

The gate is checked on initial render and on every Livewire action.

Only disable the built-in authentication check when the host route provides equivalent protection:

```env
BTEKNO_FILESYSTEM_REQUIRE_AUTH=false
```

The settings directory is created with mode `0700`. The settings and lock files use mode `0600` when the operating system permits it. Credentials are never hydrated back into password fields.

Do not commit the encrypted settings file. Although its payload is encrypted, it belongs to the deployment environment.

## Runtime behavior

The package reads and applies the encrypted settings during service-provider registration. No changes to the host application's `AppServiceProvider` or `config/filesystems.php` are required.

Saving from Livewire refreshes the filesystem manager in the current PHP process. Long-running queue workers, Octane workers, and other persistent processes must be restarted after changing providers.

```bash
php artisan queue:restart
```

Use the appropriate reload command when running Laravel Octane.

## Optional publishing

The package works without publishing files. To customize it:

```bash
php artisan vendor:publish --tag=btekno-filesystem-config
php artisan vendor:publish --tag=btekno-filesystem-views
php artisan vendor:publish --tag=btekno-filesystem-translations
```

The configuration file is published to:

```text
config/btekno/filesystem.php
```

Laravel exposes it through `config('btekno.filesystem.*')`.

## Test

```bash
composer install
composer test
```
