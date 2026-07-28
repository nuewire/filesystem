<?php

declare(strict_types=1);

namespace Btekno\Filesystem\Support;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\FilesystemManager;
use Psr\Log\LoggerInterface;
use Throwable;

final class RuntimeFilesystemConfigurator
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly EncryptedJsonSettingsStore $store,
        private readonly FilesystemConfigFactory $factory,
        private readonly StorageDirectory $directories,
        private readonly LoggerInterface $logger,
        private readonly FilesystemManager $filesystems,
    ) {
    }

    /**
     * Apply settings from the encrypted file. Invalid settings fail closed to local.
     *
     * @return array<string, mixed>
     */
    public function apply(): array
    {
        try {
            $settings = $this->store->read();
        } catch (Throwable $exception) {
            $this->logger->warning('Btekno filesystem settings could not be loaded. Local storage is active.', [
                'exception' => $exception,
                'path' => $this->store->path(),
            ]);

            $settings = $this->store->defaults();
        }

        $settings = $this->normalizeProviderDirectories($settings);

        $active = in_array($settings['active'] ?? null, ['local', 's3', 'bunnycdn'], true)
            ? (string) $settings['active']
            : 'local';

        if (! $this->providerIsComplete($active, $settings)) {
            $this->logger->warning('The selected Btekno filesystem provider is incomplete. Local storage is active.', [
                'provider' => $active,
            ]);
            $active = 'local';
        }

        $prefix = trim((string) $this->config->get('btekno.filesystem.disk_prefix', 'btekno')) ?: 'btekno';

        if ($this->config->get('btekno.filesystem.host_default_disk') === null) {
            $this->config->set(
                'btekno.filesystem.host_default_disk',
                (string) $this->config->get('filesystems.default', 'local'),
            );
        }

        $registered = [];

        foreach (['local', 's3', 'bunnycdn'] as $provider) {
            if (! $this->providerIsComplete($provider, $settings)) {
                continue;
            }

            $name = $prefix.'-'.$provider;
            $this->config->set("filesystems.disks.{$name}", $this->factory->forProvider($provider, $settings));
            $registered[$provider] = $name;
            $this->filesystems->forgetDisk($name);
        }

        $activeDisk = $registered[$active] ?? $prefix.'-local';
        $activeConfig = $this->factory->forProvider($active, $settings);
        $activeDirectory = $this->directories->normalize(data_get($settings, "providers.{$active}.directory", ''));

        $this->config->set("filesystems.disks.{$prefix}", $activeConfig);
        $this->config->set('btekno.filesystem.active_provider', $active);
        $this->config->set('btekno.filesystem.active_disk', $activeDisk);
        $this->config->set('btekno.filesystem.active_directory', $activeDirectory);
        $this->config->set('btekno.filesystem.alias_disk', $prefix);
        $this->filesystems->forgetDisk($prefix);

        if ((bool) ($settings['set_as_default'] ?? $this->config->get('btekno.filesystem.set_as_default', true))) {
            $this->config->set('filesystems.default', $activeDisk);
        } else {
            $this->config->set(
                'filesystems.default',
                (string) $this->config->get('btekno.filesystem.host_default_disk', 'local'),
            );
        }

        return $settings;
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function normalizeProviderDirectories(array $settings): array
    {
        foreach (['local', 's3', 'bunnycdn'] as $provider) {
            try {
                data_set(
                    $settings,
                    "providers.{$provider}.directory",
                    $this->directories->normalize(data_get($settings, "providers.{$provider}.directory", '')),
                );
            } catch (Throwable $exception) {
                $this->logger->warning('An invalid Btekno filesystem base directory was ignored.', [
                    'provider' => $provider,
                    'exception' => $exception,
                ]);

                data_set($settings, "providers.{$provider}.directory", '');
            }
        }

        return $settings;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function providerIsComplete(string $provider, array $settings): bool
    {
        if ($provider === 'local') {
            return true;
        }

        $providerSettings = (array) data_get($settings, "providers.{$provider}", []);

        return match ($provider) {
            's3' => $this->filled($providerSettings, ['key', 'secret', 'region', 'bucket']),
            'bunnycdn' => $this->filled($providerSettings, ['storage_zone', 'password', 'endpoint', 'cdn_url']),
            default => false,
        };
    }

    /**
     * @param array<string, mixed> $values
     * @param list<string> $keys
     */
    private function filled(array $values, array $keys): bool
    {
        foreach ($keys as $key) {
            if (trim((string) ($values[$key] ?? '')) === '') {
                return false;
            }
        }

        return true;
    }
}
