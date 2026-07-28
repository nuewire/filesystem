<?php

declare(strict_types=1);

namespace Nuewire\Filesystem\Support;

use InvalidArgumentException;

final class StorageDirectory
{
    public function normalize(mixed $value, ?string $locale = null): string
    {
        $locale ??= (string) config('nuewire.filesystem.locale', 'id');
        $directory = trim((string) $value);
        $directory = str_replace('\\', '/', $directory);
        $directory = preg_replace('#/+#', '/', $directory) ?? $directory;
        $directory = trim($directory, '/');

        if ($directory === '') {
            return '';
        }

        if (strlen($directory) > 1024) {
            throw new InvalidArgumentException($this->translate('errors.directory_too_long', $locale));
        }

        foreach (explode('/', $directory) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidArgumentException($this->translate('errors.directory_invalid_segment', $locale));
            }

            if (strlen($segment) > 255) {
                throw new InvalidArgumentException($this->translate('errors.directory_segment_too_long', $locale));
            }

            if (! preg_match('/^[A-Za-z0-9._-]+$/', $segment)) {
                throw new InvalidArgumentException($this->translate('errors.directory_invalid_characters', $locale));
            }
        }

        return $directory;
    }

    public function appendToPath(string $base, string $directory): string
    {
        $directory = $this->normalize($directory);
        $base = rtrim($base, '/\\');

        if ($directory === '') {
            if ($base === '') {
                return DIRECTORY_SEPARATOR;
            }

            return preg_match('/^[A-Za-z]:$/', $base) ? $base.DIRECTORY_SEPARATOR : $base;
        }

        return $base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $directory);
    }

    public function appendToUrl(string $base, string $directory): string
    {
        $directory = $this->normalize($directory);

        if ($directory === '') {
            return rtrim($base, '/');
        }

        return rtrim($base, '/').'/'.$directory;
    }

    private function translate(string $key, string $locale): string
    {
        return (string) trans("nuewire-filesystem::filesystem.{$key}", [], $locale);
    }
}
