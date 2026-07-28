<?php

declare(strict_types=1);

namespace Nuewire\Filesystem\Tests;

use Nuewire\Filesystem\FilesystemServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilesystemServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('app.locale', 'en');
        $app['config']->set('nuewire.filesystem.locale', 'id');
        $app['config']->set('nuewire.filesystem.authorization.require_authenticated_user', false);
        $app['config']->set(
            'nuewire.filesystem.settings_path',
            $app->storagePath('app/private/.nuewire/filesystem.json'),
        );
    }
}
