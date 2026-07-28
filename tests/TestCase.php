<?php

declare(strict_types=1);

namespace Btekno\Filesystem\Tests;

use Btekno\Filesystem\FilesystemServiceProvider;
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
        $app['config']->set('btekno.filesystem.locale', 'id');
        $app['config']->set('btekno.filesystem.authorization.require_authenticated_user', false);
        $app['config']->set(
            'btekno.filesystem.settings_path',
            $app->storagePath('app/private/.btekno/filesystem.json'),
        );
    }
}
