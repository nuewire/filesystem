<?php

declare(strict_types=1);

namespace Btekno\Filesystem\Tests;

use Btekno\Filesystem\Support\StorageDirectory;
use InvalidArgumentException;

final class StorageDirectoryTest extends TestCase
{
    public function test_it_normalizes_a_relative_storage_directory(): void
    {
        $directories = $this->app->make(StorageDirectory::class);

        self::assertSame('my-app/media', $directories->normalize('/my-app//media/'));
    }

    public function test_it_rejects_parent_directory_segments(): void
    {
        $directories = $this->app->make(StorageDirectory::class);

        $this->expectException(InvalidArgumentException::class);
        $directories->normalize('media/../private');
    }

    public function test_it_appends_directories_to_paths_and_urls(): void
    {
        $directories = $this->app->make(StorageDirectory::class);

        self::assertSame(
            'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'avatars',
            $directories->appendToPath('storage/app/public', 'media/avatars'),
        );
        self::assertSame(
            'https://example.test/storage/media/avatars',
            $directories->appendToUrl('https://example.test/storage/', '/media/avatars/'),
        );
    }
}
