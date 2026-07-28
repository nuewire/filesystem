<?php

declare(strict_types=1);

namespace Nuewire\Filesystem\Tests;

use Nuewire\Filesystem\Support\EncryptedJsonSettingsStore;
use Nuewire\Filesystem\Support\RuntimeFilesystemConfigurator;

final class RuntimeFilesystemConfiguratorTest extends TestCase
{
    public function test_local_is_the_default_provider_without_a_settings_file(): void
    {
        $this->app->make(RuntimeFilesystemConfigurator::class)->apply();

        self::assertSame('local', config('nuewire.filesystem.active_provider'));
        self::assertSame('nuewire-local', config('nuewire.filesystem.active_disk'));
        self::assertSame('nuewire-local', config('filesystems.default'));
        self::assertSame('local', config('filesystems.disks.nuewire-local.driver'));
    }

    public function test_it_applies_a_local_base_directory_to_root_and_url(): void
    {
        config()->set('app.url', 'https://example.test');
        config()->set('nuewire.filesystem.local.url', 'https://example.test/storage');

        $store = $this->app->make(EncryptedJsonSettingsStore::class);
        $settings = $store->defaults();
        $settings['providers']['local']['directory'] = '/media//uploads/';
        $store->write($settings);

        $this->app->make(RuntimeFilesystemConfigurator::class)->apply();

        self::assertSame('media/uploads', config('nuewire.filesystem.active_directory'));
        self::assertSame(
            rtrim(storage_path('app/public'), '/\\').DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'uploads',
            config('filesystems.disks.nuewire-local.root'),
        );
        self::assertSame(
            'https://example.test/storage/media/uploads',
            config('filesystems.disks.nuewire-local.url'),
        );
    }

    public function test_it_can_leave_the_host_default_disk_unchanged(): void
    {
        config()->set('filesystems.default', 'host-disk');

        $store = $this->app->make(EncryptedJsonSettingsStore::class);
        $settings = $store->defaults();
        $settings['set_as_default'] = false;
        $store->write($settings);

        $this->app->make(RuntimeFilesystemConfigurator::class)->apply();

        self::assertSame('host-disk', config('filesystems.default'));
        self::assertSame('nuewire-local', config('nuewire.filesystem.active_disk'));
    }

    public function test_it_registers_named_cloud_disks_and_selects_the_active_one(): void
    {
        $store = $this->app->make(EncryptedJsonSettingsStore::class);
        $settings = $store->defaults();
        $settings['active'] = 'bunnycdn';
        $settings['providers']['bunnycdn'] = [
            'storage_zone' => 'example-zone',
            'password' => 'secret',
            'region' => 'auto',
            'endpoint' => 'https://sg-s3.storage.bunnycdn.com',
            'cdn_url' => 'https://example.b-cdn.net',
            'directory' => 'applications/nongki',
        ];
        $store->write($settings);

        $this->app->make(RuntimeFilesystemConfigurator::class)->apply();

        self::assertSame('bunnycdn', config('nuewire.filesystem.active_provider'));
        self::assertSame('nuewire-bunnycdn', config('filesystems.default'));
        self::assertSame('s3', config('filesystems.disks.nuewire-bunnycdn.driver'));
        self::assertTrue(config('filesystems.disks.nuewire-bunnycdn.use_path_style_endpoint'));
        self::assertSame('applications/nongki', config('filesystems.disks.nuewire-bunnycdn.root'));
        self::assertSame('applications/nongki', config('nuewire.filesystem.active_directory'));
    }
}
