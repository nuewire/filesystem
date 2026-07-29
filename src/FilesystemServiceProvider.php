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
        $this->registerPlatformDashboard();
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
        $registrar->register('nuewire-filesystem', Filesystem::class);
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
                    'component' => 'nuewire-filesystem',
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
                'component' => 'nuewire-filesystem',
                'permission' => 'filesystem.view',
                'icon' => 'storage',
                'order' => 30,
            ]);
        });
    }


    private function registerPlatformDashboard(): void
    {
        $registryClass = 'Nuewire\\Platform\\Dashboard\\DashboardRegistry';

        $this->app->afterResolving($registryClass, static function (object $registry): void {
            if (! method_exists($registry, 'register')) {
                return;
            }

            if (method_exists($registry, 'registerGroup')) {
                $registry->registerGroup('storage', [
                    'label' => ['id' => 'Storage', 'en' => 'Storage'],
                    'order' => 60,
                ]);
            }

            $registry->register('filesystem.active-disk', [
                'group' => 'storage',
                'label' => ['id' => 'Storage Aktif', 'en' => 'Active Storage'],
                'description' => ['id' => 'Provider dan disk filesystem yang sedang aktif.', 'en' => 'Currently active filesystem provider and disk.'],
                'type' => 'status',
                'permission' => 'filesystem.view',
                'width' => 3,
                'default' => true,
                'cache_ttl' => 120,
                'cache_scope' => 'global',
                'resolver' => static function (object $context): array {
                    $provider = (string) config('nuewire.filesystem.active_provider', 'local');
                    $disk = (string) config('nuewire.filesystem.active_disk', config('filesystems.default', 'local'));
                    $directory = (string) config('nuewire.filesystem.active_directory', '');

                    return [
                        'status' => $disk !== '' ? 'healthy' : 'danger',
                        'headline' => $disk !== '' ? $disk : ($context->locale === 'en' ? 'No active disk' : 'Tidak ada disk aktif'),
                        'message' => $context->locale === 'en' ? 'Laravel filesystem destination' : 'Tujuan filesystem Laravel',
                        'items' => [
                            ['label' => 'Provider', 'value' => $provider],
                            ['label' => $context->locale === 'en' ? 'Directory' : 'Direktori', 'value' => $directory !== '' ? $directory : '/'],
                        ],
                        'url' => $context->route('settings', 'storage'),
                    ];
                },
                'order' => 10,
            ]);

            $registry->register('filesystem.storage-usage', [
                'group' => 'storage',
                'label' => ['id' => 'Penggunaan Storage', 'en' => 'Storage Usage'],
                'description' => ['id' => 'Ukuran disk lokal aktif; remote disk tidak dipindai otomatis.', 'en' => 'Active local disk size; remote disks are not scanned automatically.'],
                'type' => 'stat',
                'permission' => 'filesystem.view',
                'width' => 4,
                'default' => false,
                'cache_ttl' => 900,
                'cache_scope' => 'global',
                'resolver' => static function (object $context): array {
                    $disk = (string) config('nuewire.filesystem.active_disk', config('filesystems.default', 'local'));
                    $diskConfig = (array) config("filesystems.disks.{$disk}", []);
                    $driver = (string) ($diskConfig['driver'] ?? '');

                    if ($driver !== 'local') {
                        return [
                            'value' => '—',
                            'meta' => $context->locale === 'en' ? 'Remote usage scan is disabled' : 'Pemindaian remote dinonaktifkan',
                            'url' => $context->route('settings', 'storage'),
                        ];
                    }

                    $root = (string) ($diskConfig['root'] ?? '');
                    $bytes = 0;
                    $files = 0;

                    if ($root !== '' && is_dir($root)) {
                        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
                        foreach ($iterator as $file) {
                            if ($file->isFile()) {
                                $bytes += $file->getSize();
                                if (++$files >= 100000) break;
                            }
                        }
                    }

                    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                    $size = (float) $bytes;
                    $unit = 0;
                    while ($size >= 1024 && $unit < count($units) - 1) {$size /= 1024; $unit++;}

                    return [
                        'value' => number_format($size, $unit === 0 ? 0 : 1).' '.$units[$unit],
                        'meta' => number_format($files).' '.($context->locale === 'en' ? 'files scanned' : 'file dipindai'),
                        'url' => $context->route('settings', 'storage'),
                    ];
                },
                'order' => 20,
            ]);

            $registry->register('filesystem.providers', [
                'group' => 'storage',
                'label' => ['id' => 'Provider Tersedia', 'en' => 'Available Providers'],
                'description' => ['id' => 'Provider storage yang siap digunakan saat runtime.', 'en' => 'Storage providers available at runtime.'],
                'type' => 'stat',
                'permission' => 'filesystem.view',
                'width' => 4,
                'default' => false,
                'cache_ttl' => 300,
                'cache_scope' => 'global',
                'resolver' => static function (object $context): array {
                    $prefix = trim((string) config('nuewire.filesystem.disk_prefix', 'nuewire')) ?: 'nuewire';
                    $disks = (array) config('filesystems.disks', []);
                    $providers = array_values(array_filter(array_keys($disks), static fn (string $name): bool => $name === $prefix || str_starts_with($name, $prefix.'-')));

                    return [
                        'value' => number_format(count($providers)),
                        'meta' => implode(', ', $providers),
                        'url' => $context->route('settings', 'storage'),
                    ];
                },
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
