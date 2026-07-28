<?php

declare(strict_types=1);

namespace Nuewire\Filesystem\Tests;

use Nuewire\Filesystem\FilesystemServiceProvider;
use Illuminate\Support\ServiceProvider;

final class ConfigurationTest extends TestCase
{
    public function test_configuration_uses_the_nested_nuewire_key(): void
    {
        self::assertSame('id', config('nuewire.filesystem.locale'));
        self::assertSame(
            storage_path('app/private/.nuewire/filesystem.json'),
            config('nuewire.filesystem.settings_path'),
        );
    }

    public function test_configuration_is_published_to_the_nuewire_directory(): void
    {
        $paths = ServiceProvider::pathsToPublish(
            FilesystemServiceProvider::class,
            'nuewire-filesystem-config',
        );

        self::assertContains(config_path('nuewire/filesystem.php'), array_values($paths));
    }

    public function test_views_and_translations_use_the_shared_vendor_directory(): void
    {
        $viewPaths = ServiceProvider::pathsToPublish(
            FilesystemServiceProvider::class,
            'nuewire-filesystem-views',
        );

        $translationPaths = ServiceProvider::pathsToPublish(
            FilesystemServiceProvider::class,
            'nuewire-filesystem-translations',
        );

        self::assertContains(
            resource_path('views/vendor/nuewire/filesystem'),
            array_values($viewPaths),
        );
        self::assertContains(
            lang_path('vendor/nuewire/filesystem'),
            array_values($translationPaths),
        );
    }
}
