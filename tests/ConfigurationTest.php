<?php

declare(strict_types=1);

namespace Btekno\Filesystem\Tests;

use Btekno\Filesystem\FilesystemServiceProvider;
use Illuminate\Support\ServiceProvider;

final class ConfigurationTest extends TestCase
{
    public function test_configuration_uses_the_nested_btekno_key(): void
    {
        self::assertSame('id', config('btekno.filesystem.locale'));
        self::assertSame(
            storage_path('app/private/.btekno/filesystem.json'),
            config('btekno.filesystem.settings_path'),
        );
    }

    public function test_configuration_is_published_to_the_btekno_directory(): void
    {
        $paths = ServiceProvider::pathsToPublish(
            FilesystemServiceProvider::class,
            'btekno-filesystem-config',
        );

        self::assertContains(config_path('btekno/filesystem.php'), array_values($paths));
    }
}
