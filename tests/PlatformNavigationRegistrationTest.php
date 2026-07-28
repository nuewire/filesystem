<?php

declare(strict_types=1);

namespace Nuewire\Filesystem\Tests;

use PHPUnit\Framework\Attributes\Test;

final class PlatformNavigationRegistrationTest extends TestCase
{
    #[Test]
    public function it_registers_navigation_when_the_platform_registry_is_resolved_later(): void
    {
        $abstract = 'Nuewire\\Platform\\Navigation\\NavigationRegistry';

        $this->app->singleton($abstract, static fn (): FakeFilesystemNavigationRegistry => new FakeFilesystemNavigationRegistry());

        /** @var FakeFilesystemNavigationRegistry $registry */
        $registry = $this->app->make($abstract);

        self::assertArrayHasKey('filesystem', $registry->pages);
        self::assertSame('nuewire::filesystem', $registry->pages['filesystem']['component']);
    }
}

final class FakeFilesystemNavigationRegistry
{
    /** @var array<string, array<string, mixed>> */
    public array $pages = [];

    /** @param array<string, mixed> $page */
    public function register(string $id, array $page): self
    {
        $this->pages[$id] = $page;

        return $this;
    }
}
