<?php

declare(strict_types=1);

namespace Nuewire\Filesystem\Tests;

use PHPUnit\Framework\Attributes\Test;

final class PlatformNavigationRegistrationTest extends TestCase
{
    #[Test]
    public function it_registers_storage_under_settings_configuration(): void
    {
        $abstract = 'Nuewire\\Platform\\Navigation\\NavigationRegistry';
        $this->app->singleton($abstract, static fn (): FakeFilesystemNavigationRegistry => new FakeFilesystemNavigationRegistry());

        /** @var FakeFilesystemNavigationRegistry $registry */
        $registry = $this->app->make($abstract);

        self::assertArrayHasKey('filesystem.settings', $registry->pages);
        self::assertSame('settings', $registry->pages['filesystem.settings']['area']);
        self::assertSame('configuration', $registry->pages['filesystem.settings']['group']);
        self::assertSame('storage', $registry->pages['filesystem.settings']['slug']);
        self::assertSame('nuewire::filesystem', $registry->pages['filesystem.settings']['component']);
    }
}

final class FakeFilesystemNavigationRegistry
{
    /** @var array<string, array<string, mixed>> */
    public array $pages = [];

    /** @param array<string, mixed> $area */
    public function registerArea(string $id, array $area = []): self
    {
        return $this;
    }

    /** @param array<string, mixed> $page */
    public function register(string $id, array $page): self
    {
        $this->pages[$id] = $page;

        return $this;
    }
}
