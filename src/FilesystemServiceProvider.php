<?php

declare(strict_types=1);

namespace Nuewire\Filesystem;

use Nuewire\Filesystem\Livewire\Filesystem;
use Nuewire\Filesystem\Support\ConnectionTester;
use Nuewire\Filesystem\Support\EncryptedJsonSettingsStore;
use Nuewire\Filesystem\Support\FilesystemConfigFactory;
use Nuewire\Filesystem\Support\RuntimeFilesystemConfigurator;
use Nuewire\Filesystem\Support\StorageDirectory;
use Illuminate\Filesystem\Filesystem as LaravelFilesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Psr\Log\LoggerInterface;

final class FilesystemServiceProvider extends ServiceProvider
{
    private const CONFIG_KEY = 'nuewire.filesystem';

    public function register(): void
    {
        $this->replaceConfigRecursivelyFrom(__DIR__.'/../config/nuewire/filesystem.php', self::CONFIG_KEY);
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

        $this->registerPlatformNavigation();

        // Apply before application providers start using the default filesystem.
        $this->app->make(RuntimeFilesystemConfigurator::class)->apply();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nuewire-filesystem');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'nuewire-filesystem');

        $this->registerLivewireComponent();

        $this->publishes([
            __DIR__.'/../config/nuewire/filesystem.php' => config_path('nuewire/filesystem.php'),
        ], 'nuewire-filesystem-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/nuewire/filesystem'),
        ], 'nuewire-filesystem-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/nuewire/filesystem'),
        ], 'nuewire-filesystem-translations');
    }

    private function registerLivewireComponent(): void
    {
        $livewire = $this->app->make('livewire');

        if (method_exists($livewire, 'addNamespace')) {
            Livewire::resolveMissingComponent(
                static fn (string $name): ?string => $name === 'nuewire::filesystem'
                    ? Filesystem::class
                    : null,
            );

            return;
        }

        Livewire::component('nuewire::filesystem', Filesystem::class);
    }

    private function registerPlatformNavigation(): void
    {
        $registryClass = 'Nuewire\\Platform\\Navigation\\NavigationRegistry';

        $this->app->afterResolving($registryClass, static function (object $registry): void {
            if (! method_exists($registry, 'register')) {
                return;
            }

            $registry->register('filesystem', [
                'label' => ['id' => 'Filesystem', 'en' => 'Filesystem'],
                'description' => ['id' => 'Atur lokasi penyimpanan file.', 'en' => 'Configure file storage.'],
                'group' => ['id' => 'Pengaturan', 'en' => 'Settings'],
                'component' => 'nuewire::filesystem',
                'icon' => 'F',
                'order' => 20,
            ]);
        });
    }
}
