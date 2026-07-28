<?php

declare(strict_types=1);

namespace Btekno\Filesystem\Tests;

use Btekno\Filesystem\Support\EncryptedJsonSettingsStore;

final class EncryptedJsonSettingsStoreTest extends TestCase
{
    public function test_it_persists_an_encrypted_json_envelope(): void
    {
        $store = $this->app->make(EncryptedJsonSettingsStore::class);
        $settings = $store->defaults();
        $settings['active'] = 's3';
        $settings['providers']['s3']['secret'] = 'super-secret-value';

        $store->write($settings);

        $raw = file_get_contents($store->path());

        self::assertIsString($raw);
        self::assertStringNotContainsString('super-secret-value', $raw);
        self::assertSame('s3', $store->read()['active']);
        self::assertSame('super-secret-value', $store->read()['providers']['s3']['secret']);
    }

    public function test_it_uses_the_shared_btekno_directory(): void
    {
        $store = $this->app->make(EncryptedJsonSettingsStore::class);

        self::assertSame(
            $this->app->storagePath('app/private/.btekno/filesystem.json'),
            $store->path(),
        );
    }
}
