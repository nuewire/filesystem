<?php

declare(strict_types=1);

namespace Btekno\Filesystem\Support;

use InvalidArgumentException;

final class FilesystemConfigFactory
{
    public function __construct(
        private readonly StorageDirectory $directories,
    ) {
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function forProvider(string $provider, array $settings, bool $throw = false): array
    {
        $providers = is_array($settings['providers'] ?? null) ? $settings['providers'] : [];

        return match ($provider) {
            'local' => $this->local((array) ($providers['local'] ?? []), $throw),
            's3' => $this->s3((array) ($providers['s3'] ?? []), $throw),
            'bunnycdn' => $this->bunny((array) ($providers['bunnycdn'] ?? []), $throw),
            default => throw new InvalidArgumentException("Unsupported filesystem provider [{$provider}]."),
        };
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function local(array $settings, bool $throw): array
    {
        $directory = $this->directories->normalize($settings['directory'] ?? '');
        $root = (string) config('btekno.filesystem.local.root', storage_path('app/public'));
        $url = (string) config('btekno.filesystem.local.url', rtrim((string) config('app.url'), '/').'/storage');

        return [
            'driver' => 'local',
            'root' => $this->directories->appendToPath($root, $directory),
            'url' => $this->directories->appendToUrl($url, $directory),
            'visibility' => (string) config('btekno.filesystem.local.visibility', 'public'),
            'throw' => $throw,
        ];
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function s3(array $settings, bool $throw): array
    {
        $directory = $this->directories->normalize($settings['directory'] ?? '');

        return array_filter([
            'driver' => 's3',
            'key' => (string) ($settings['key'] ?? ''),
            'secret' => (string) ($settings['secret'] ?? ''),
            'region' => (string) ($settings['region'] ?? ''),
            'bucket' => (string) ($settings['bucket'] ?? ''),
            'root' => $directory === '' ? null : $directory,
            'url' => $this->nullableString($settings['url'] ?? null),
            'endpoint' => $this->nullableString($settings['endpoint'] ?? null),
            'use_path_style_endpoint' => (bool) ($settings['use_path_style_endpoint'] ?? false),
            'throw' => $throw,
        ], static fn (mixed $value, string $key): bool => ! in_array($key, ['root', 'url', 'endpoint'], true) || $value !== null, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function bunny(array $settings, bool $throw): array
    {
        $zone = (string) ($settings['storage_zone'] ?? '');
        $directory = $this->directories->normalize($settings['directory'] ?? '');

        return array_filter([
            'driver' => 's3',
            'key' => $zone,
            'secret' => (string) ($settings['password'] ?? ''),
            'region' => (string) ($settings['region'] ?? 'auto'),
            'bucket' => $zone,
            'root' => $directory === '' ? null : $directory,
            'endpoint' => rtrim((string) ($settings['endpoint'] ?? ''), '/'),
            'url' => rtrim((string) ($settings['cdn_url'] ?? ''), '/'),
            'use_path_style_endpoint' => true,
            'throw' => $throw,
        ], static fn (mixed $value, string $key): bool => $key !== 'root' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
