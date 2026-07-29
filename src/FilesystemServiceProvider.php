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
use Nuewire\Support\LivewireComponentRegistrar;
use Nuewire\Support\NuewirePaths;
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
        $this->registerAclPermissions();

        // Apply before application providers start using the default filesystem.
        $this->app->make(RuntimeFilesystemConfigurator::class)->apply();
    }

    public function boot(): void
    {
        $paths = $this->app->make(NuewirePaths::class);
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nuewire-filesystem');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'nuewire-filesystem');

        $this->registerLivewireComponent();

        $this->publishes([
            __DIR__.'/../config/nuewire/filesystem.php' => $paths->configFile('filesystem'),
        ], 'nuewire-filesystem-config');

        $this->publishes([
            __DIR__.'/../resources/views' => $paths->publishedViews('filesystem'),
        ], 'nuewire-filesystem-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => $paths->publishedTranslations('filesystem'),
        ], 'nuewire-filesystem-translations');
    }

    private function registerLivewireComponent(): void
    {
        $registrar = $this->app->make(LivewireComponentRegistrar::class);
        $registrar->register('nuewire::filesystem', Filesystem::class);
    }

    private function registerPlatformNavigation(): void
    {
        $registryClass = 'Nuewire\Platform\Navigation\NavigationRegistry';

        $this->app->afterResolving($registryClass, static function (object $registry): void {
            if (! method_exists($registry, 'register')) {
                return;
            }

            if (! method_exists($registry, 'registerArea')) {
                $registry->register('filesystem', [
                    'label' => ['id' => 'Filesystem', 'en' => 'Filesystem'],
                    'description' => ['id' => 'Atur lokasi penyimpanan file.', 'en' => 'Configure file storage.'],
                    'group' => ['id' => 'Pengaturan', 'en' => 'Settings'],
                    'component' => 'nuewire::filesystem',
                    'permission' => 'filesystem.view',
                    'icon' => 'F',
                    'order' => 20,
                ]);

                return;
            }

            $registry->register('filesystem.settings', [
                'area' => 'settings',
                'group' => 'configuration',
                'slug' => 'storage',
                'aliases' => ['filesystem'],
                'label' => ['id' => 'Storage', 'en' => 'Storage'],
                'description' => ['id' => 'Atur lokasi penyimpanan file.', 'en' => 'Configure file storage.'],
                'component' => 'nuewire::filesystem',
                'permission' => 'filesystem.view',
                'icon' => 'storage',
                'order' => 30,
            ]);
        });
    }

    private function registerAclPermissions(): void
    {
        $registryClass = 'Nuewire\\Acl\\Registry\\PermissionRegistry';

        $this->app->afterResolving($registryClass, static function (object $registry): void {
            if (! method_exists($registry, 'registerMany')) {
                return;
            }

            $registry->registerMany([
                'filesystem.view' => ['id' => 'Melihat pengaturan filesystem', 'en' => 'View filesystem settings'],
                'filesystem.manage' => ['id' => 'Mengubah pengaturan filesystem', 'en' => 'Manage filesystem settings'],
            ], 'filesystem');
        });
    }
}
