<?php

declare(strict_types=1);

namespace Nuewire\Filesystem\Tests;

use Livewire\Livewire;

final class FilesystemComponentRegistrationTest extends TestCase
{
    public function test_namespaced_filesystem_component_is_registered_in_indonesian_by_default(): void
    {
        Livewire::test('nuewire::filesystem')
            ->assertStatus(200)
            ->assertSet('locale', 'id')
            ->assertSee('Pilih penyedia penyimpanan')
            ->assertSee('Simpan pengaturan');
    }

    public function test_component_can_be_rendered_in_english(): void
    {
        Livewire::test('nuewire::filesystem', ['locale' => 'en'])
            ->assertStatus(200)
            ->assertSet('locale', 'en')
            ->assertSee('Select the storage provider')
            ->assertSee('Save settings');
    }

    public function test_locale_can_be_changed_from_the_component(): void
    {
        Livewire::test('nuewire::filesystem')
            ->set('locale', 'en')
            ->assertSet('locale', 'en')
            ->assertSee('Test connection');
    }
}
