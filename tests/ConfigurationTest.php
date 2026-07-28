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
}
