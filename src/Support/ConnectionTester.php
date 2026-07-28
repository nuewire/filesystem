<?php

declare(strict_types=1);

namespace Btekno\Filesystem\Support;

use Illuminate\Filesystem\FilesystemManager;
use RuntimeException;
use Throwable;

final class ConnectionTester
{
    public function __construct(
        private readonly FilesystemManager $filesystems,
        private readonly FilesystemConfigFactory $factory,
    ) {
    }

    /**
     * @param array<string, mixed> $settings
     * @return array{message: string, url: string|null}
     */
    public function test(string $provider, array $settings, ?string $locale = null): array
    {
        $locale ??= (string) config('btekno.filesystem.locale', 'id');
        $disk = $this->filesystems->build($this->factory->forProvider($provider, $settings, true));
        $path = '.btekno-test/'.bin2hex(random_bytes(12)).'.txt';
        $payload = 'btekno-filesystem:'.bin2hex(random_bytes(16));
        $url = null;

        try {
            if (! $disk->put($path, $payload)) {
                throw new RuntimeException($this->translate('errors.test_write', $locale));
            }

            if (! $disk->exists($path)) {
                throw new RuntimeException($this->translate('errors.test_missing', $locale));
            }

            if ($disk->get($path) !== $payload) {
                throw new RuntimeException($this->translate('errors.test_content', $locale));
            }

            try {
                $url = $disk->url($path);
            } catch (Throwable) {
                $url = null;
            }

            return [
                'message' => $this->translate('status.connection_success', $locale),
                'url' => is_string($url) && $url !== '' ? $url : null,
            ];
        } finally {
            try {
                if ($disk->exists($path)) {
                    $disk->delete($path);
                }
            } catch (Throwable) {
                // The original connection error remains the useful exception.
            }
        }
    }

    private function translate(string $key, string $locale): string
    {
        return (string) trans("btekno-filesystem::filesystem.{$key}", [], $locale);
    }
}
