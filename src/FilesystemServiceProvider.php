<?php

declare(strict_types=1);

namespace Btekno\Filesystem;

use Btekno\Filesystem\Livewire\Filesystem;
use Btekno\Filesystem\Support\ConnectionTester;
use Btekno\Filesystem\Support\EncryptedJsonSettingsStore;
use Btekno\Filesystem\Support\FilesystemConfigFactory;
use Btekno\Filesystem\Support\RuntimeFilesystemConfigurator;
use Btekno\Filesystem\Support\StorageDirectory;
use Illuminate\Filesystem\Filesystem as LaravelFilesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Psr\Log\LoggerInterface;

final class FilesystemServiceProvider extends ServiceProvider
{
    private const CONFIG_KEY = 'btekno.filesystem';

    public function register(): void
    {
        $this->replaceConfigRecursivelyFrom(__DIR__.'/../config/btekno/filesystem.php', self::CONFIG_KEY);
        $this->app->singleton(FilesystemConfigFactory::class);

        $this->app->singleton(EncryptedJsonSettingsStore::class, function ($app): EncryptedJsonSettingsStore {
            return new EncryptedJsonSettingsStore(
                $app,
                $app->make(LaravelFilesystem::class),
                (string) $app['config']->get(self::CONFIG_KEY.'.settings_path'),
            );
        });

        $this->app->singleton(RuntimeFilesystemConfigurator::class, function ($app): RuntimeFilesystemConfigurator {
            return new RuntimeFilesystemConfigurator(
                $app['config'],
                $app->make(EncryptedJsonSettingsStore::class),
                $app->make(FilesystemConfigFactory::class),
                $app->make(StorageDirectory::class),
                $app->make(LoggerInterface::class),
                $app->make(FilesystemManager::class),
            );
        });

        $this->app->singleton(ConnectionTester::class, function ($app): ConnectionTester {
            return new ConnectionTester(
                $app->make(FilesystemManager::class),
                $app->make(FilesystemConfigFactory::class),
            );
        });

        // Apply before application providers start using the default filesystem.
        $this->app->make(RuntimeFilesystemConfigurator::class)->apply();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'btekno-filesystem');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'btekno-filesystem');

        $this->registerLivewireComponent();
        $this->registerPlatformNavigation();

        $this->publishes([
            __DIR__.'/../config/btekno/filesystem.php' => config_path('btekno/filesystem.php'),
        ], 'btekno-filesystem-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/btekno-filesystem'),
        ], 'btekno-filesystem-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/btekno-filesystem'),
        ], 'btekno-filesystem-translations');
    }

    private function registerLivewireComponent(): void
    {
        $livewire = $this->app->make('livewire');

        if (method_exists($livewire, 'addNamespace')) {
            Livewire::resolveMissingComponent(
                static fn (string $name): ?string => $name === 'btekno::filesystem'
                    ? Filesystem::class
                    : null,
            );

            return;
        }

        Livewire::component('btekno::filesystem', Filesystem::class);
    }

    private function registerPlatformNavigation(): void
    {
        $registryClass = 'Btekno\\Platform\\Navigation\\NavigationRegistry';

        if (! $this->app->bound($registryClass)) {
            return;
        }

        $this->app->make($registryClass)->register('filesystem', [
            'label' => ['id' => 'Filesystem', 'en' => 'Filesystem'],
            'description' => ['id' => 'Atur lokasi penyimpanan file.', 'en' => 'Configure file storage.'],
            'group' => ['id' => 'Pengaturan', 'en' => 'Settings'],
            'component' => 'btekno::filesystem',
            'icon' => 'F',
            'order' => 20,
        ]);
    }
}
