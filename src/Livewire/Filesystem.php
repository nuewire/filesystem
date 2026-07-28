<?php

declare(strict_types=1);

namespace Nuewire\Filesystem\Livewire;

use Nuewire\Filesystem\Support\ConnectionTester;
use Nuewire\Filesystem\Support\EncryptedJsonSettingsStore;
use Nuewire\Filesystem\Support\RuntimeFilesystemConfigurator;
use Nuewire\Filesystem\Support\StorageDirectory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Component;
use Throwable;

final class Filesystem extends Component
{
    public string $locale = 'id';
    public string $driver = 'local';
    public bool $setAsDefault = true;

    public string $localDirectory = '';

    public string $s3Key = '';
    public string $s3Secret = '';
    public string $s3Region = '';
    public string $s3Bucket = '';
    public string $s3Url = '';
    public string $s3Endpoint = '';
    public bool $s3PathStyle = false;
    public bool $hasS3Secret = false;
    public string $s3Directory = '';

    public string $bunnyStorageZone = '';
    public string $bunnyPassword = '';
    public string $bunnyRegion = 'auto';
    public string $bunnyEndpoint = '';
    public string $bunnyCdnUrl = '';
    public bool $hasBunnyPassword = false;
    public string $bunnyDirectory = '';

    public ?string $status = null;
    public ?string $error = null;
    public ?string $testUrl = null;

    public function mount(EncryptedJsonSettingsStore $store, ?string $locale = null): void
    {
        $this->ensureAuthorized();
        $this->locale = $this->resolveLocale($locale);
        $this->rememberLocale();

        try {
            $this->hydrateFromSettings($store->read());
        } catch (Throwable $exception) {
            report($exception);
            $this->hydrateFromSettings($store->defaults());
            $this->error = $this->translate('errors.load_failed', ['message' => $exception->getMessage()]);
        }
    }

    public function updatedLocale(string $locale): void
    {
        $this->ensureAuthorized();
        $this->locale = $this->normalizeLocale($locale);
        $this->rememberLocale();
        $this->clearMessages();
    }

    public function save(
        EncryptedJsonSettingsStore $store,
        RuntimeFilesystemConfigurator $configurator,
        StorageDirectory $directories,
    ): void {
        $this->ensureAuthorized('filesystem.manage');
        $this->clearMessages();

        try {
            $settings = $this->settingsFromForm($store, true, $directories);
            $store->write($settings);
            $configurator->apply();
            $this->hydrateFromSettings($settings);
            $this->clearSecretInputs();
            $this->status = $this->translate('status.saved');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->error = $this->translate('errors.save_failed', ['message' => $exception->getMessage()]);
        }
    }

    public function testConnection(
        EncryptedJsonSettingsStore $store,
        ConnectionTester $tester,
        StorageDirectory $directories,
    ): void {
        $this->ensureAuthorized('filesystem.manage');
        $this->clearMessages();

        try {
            $settings = $this->settingsFromForm($store, false, $directories);
            $result = $tester->test($this->driver, $settings, $this->locale);
            $this->status = $result['message'];
            $this->testUrl = $result['url'];
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->error = $this->translate('errors.test_failed', ['message' => $exception->getMessage()]);
        }
    }

    public function useLocal(
        EncryptedJsonSettingsStore $store,
        RuntimeFilesystemConfigurator $configurator,
        StorageDirectory $directories,
    ): void {
        $this->ensureAuthorized('filesystem.manage');
        $this->driver = 'local';
        $this->save($store, $configurator, $directories);
    }

    public function render()
    {
        $this->ensureAuthorized();
        $store = app(EncryptedJsonSettingsStore::class);
        $settingsPath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $store->path());

        return view('nuewire-filesystem::livewire.filesystem', [
            'settingsPath' => $settingsPath,
            'settingsExist' => $store->exists(),
            'activeDisk' => (string) config('nuewire.filesystem.active_disk', 'nuewire-local'),
            'activeDirectory' => (string) config('nuewire.filesystem.active_directory', ''),
            'storageLinkExists' => is_link(public_path('storage')) || file_exists(public_path('storage')),
            'localeOptions' => $this->localeOptions(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsFromForm(
        EncryptedJsonSettingsStore $store,
        bool $saving,
        StorageDirectory $directories,
    ): array {
        try {
            $existing = $store->read();
        } catch (Throwable) {
            // Allow an authorized administrator to replace a corrupt or undecryptable file.
            $existing = $store->defaults();
        }

        $existingS3Secret = (string) data_get($existing, 'providers.s3.secret', '');
        $existingBunnyPassword = (string) data_get($existing, 'providers.bunnycdn.password', '');
        $directoryRules = $this->directoryRules($directories);

        $rules = [
            'driver' => ['required', Rule::in(['local', 's3', 'bunnycdn'])],
            'setAsDefault' => ['boolean'],
            'localDirectory' => $directoryRules,
            's3Directory' => $directoryRules,
            'bunnyDirectory' => $directoryRules,
        ];

        if ($this->driver === 's3') {
            $rules += [
                's3Key' => ['required', 'string', 'max:255'],
                's3Secret' => [$existingS3Secret === '' ? 'required' : 'nullable', 'string', 'max:2048'],
                's3Region' => ['required', 'string', 'max:100'],
                's3Bucket' => ['required', 'string', 'max:255'],
                's3Url' => ['nullable', 'url:http,https', 'max:2048'],
                's3Endpoint' => ['nullable', 'url:http,https', 'max:2048'],
                's3PathStyle' => ['boolean'],
            ];
        }

        if ($this->driver === 'bunnycdn') {
            $rules += [
                'bunnyStorageZone' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/'],
                'bunnyPassword' => [$existingBunnyPassword === '' ? 'required' : 'nullable', 'string', 'max:2048'],
                'bunnyRegion' => ['required', 'string', 'max:100'],
                'bunnyEndpoint' => ['required', 'url:https', 'max:2048'],
                'bunnyCdnUrl' => ['required', 'url:https', 'max:2048'],
            ];
        }

        $this->validate($rules, $this->validationMessages(), $this->validationAttributes());

        $localDirectory = $directories->normalize($this->localDirectory, $this->locale);
        $s3Directory = $directories->normalize($this->s3Directory, $this->locale);
        $bunnyDirectory = $directories->normalize($this->bunnyDirectory, $this->locale);

        $settings = array_replace_recursive($store->defaults(), $existing);
        $settings['active'] = $this->driver;
        $settings['set_as_default'] = $this->setAsDefault;
        $settings['providers']['local'] = [
            'directory' => $localDirectory,
        ];
        $settings['providers']['s3'] = [
            'key' => trim($this->s3Key),
            'secret' => $this->s3Secret !== '' ? $this->s3Secret : $existingS3Secret,
            'region' => trim($this->s3Region),
            'bucket' => trim($this->s3Bucket),
            'url' => rtrim(trim($this->s3Url), '/'),
            'endpoint' => rtrim(trim($this->s3Endpoint), '/'),
            'use_path_style_endpoint' => $this->s3PathStyle,
            'directory' => $s3Directory,
        ];
        $settings['providers']['bunnycdn'] = [
            'storage_zone' => trim($this->bunnyStorageZone),
            'password' => $this->bunnyPassword !== '' ? $this->bunnyPassword : $existingBunnyPassword,
            'region' => trim($this->bunnyRegion) ?: 'auto',
            'endpoint' => rtrim(trim($this->bunnyEndpoint), '/'),
            'cdn_url' => rtrim(trim($this->bunnyCdnUrl), '/'),
            'directory' => $bunnyDirectory,
        ];

        if ($saving) {
            $settings['updated_by'] = Auth::id();
        }

        return $settings;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function hydrateFromSettings(array $settings): void
    {
        $this->driver = in_array($settings['active'] ?? null, ['local', 's3', 'bunnycdn'], true)
            ? (string) $settings['active']
            : 'local';
        $this->setAsDefault = (bool) ($settings['set_as_default'] ?? true);
        $this->localDirectory = (string) data_get($settings, 'providers.local.directory', '');

        $this->s3Key = (string) data_get($settings, 'providers.s3.key', '');
        $this->s3Region = (string) data_get($settings, 'providers.s3.region', '');
        $this->s3Bucket = (string) data_get($settings, 'providers.s3.bucket', '');
        $this->s3Url = (string) data_get($settings, 'providers.s3.url', '');
        $this->s3Endpoint = (string) data_get($settings, 'providers.s3.endpoint', '');
        $this->s3PathStyle = (bool) data_get($settings, 'providers.s3.use_path_style_endpoint', false);
        $this->hasS3Secret = trim((string) data_get($settings, 'providers.s3.secret', '')) !== '';
        $this->s3Directory = (string) data_get($settings, 'providers.s3.directory', '');

        $this->bunnyStorageZone = (string) data_get($settings, 'providers.bunnycdn.storage_zone', '');
        $this->bunnyRegion = (string) data_get($settings, 'providers.bunnycdn.region', 'auto');
        $this->bunnyEndpoint = (string) data_get($settings, 'providers.bunnycdn.endpoint', '');
        $this->bunnyCdnUrl = (string) data_get($settings, 'providers.bunnycdn.cdn_url', '');
        $this->hasBunnyPassword = trim((string) data_get($settings, 'providers.bunnycdn.password', '')) !== '';
        $this->bunnyDirectory = (string) data_get($settings, 'providers.bunnycdn.directory', '');

        $this->clearSecretInputs();
    }

    /**
     * @return array<int, mixed>
     */
    private function directoryRules(StorageDirectory $directories): array
    {
        return [
            'nullable',
            'string',
            'max:1024',
            function (string $attribute, mixed $value, callable $fail) use ($directories): void {
                try {
                    $directories->normalize($value, $this->locale);
                } catch (InvalidArgumentException $exception) {
                    $fail($exception->getMessage());
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'required' => $this->translate('validation.required'),
            'string' => $this->translate('validation.string'),
            'max' => $this->translate('validation.max'),
            'url' => $this->translate('validation.url'),
            'boolean' => $this->translate('validation.boolean'),
            'in' => $this->translate('validation.in'),
            'regex' => $this->translate('validation.regex'),
            'bunnyEndpoint.url' => $this->translate('validation.https_url'),
            'bunnyCdnUrl.url' => $this->translate('validation.https_url'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'driver' => $this->translate('validation.attributes.driver'),
            'setAsDefault' => $this->translate('validation.attributes.set_as_default'),
            'localDirectory' => $this->translate('validation.attributes.local_directory'),
            's3Key' => $this->translate('validation.attributes.s3_key'),
            's3Secret' => $this->translate('validation.attributes.s3_secret'),
            's3Region' => $this->translate('validation.attributes.s3_region'),
            's3Bucket' => $this->translate('validation.attributes.s3_bucket'),
            's3Url' => $this->translate('validation.attributes.s3_url'),
            's3Endpoint' => $this->translate('validation.attributes.s3_endpoint'),
            's3PathStyle' => $this->translate('validation.attributes.s3_path_style'),
            's3Directory' => $this->translate('validation.attributes.s3_directory'),
            'bunnyStorageZone' => $this->translate('validation.attributes.bunny_zone'),
            'bunnyPassword' => $this->translate('validation.attributes.bunny_password'),
            'bunnyRegion' => $this->translate('validation.attributes.bunny_region'),
            'bunnyEndpoint' => $this->translate('validation.attributes.bunny_endpoint'),
            'bunnyCdnUrl' => $this->translate('validation.attributes.bunny_cdn_url'),
            'bunnyDirectory' => $this->translate('validation.attributes.bunny_directory'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function localeOptions(): array
    {
        $options = [];

        foreach ($this->supportedLocales() as $locale) {
            $options[$locale] = $this->translate("language.{$locale}");
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    private function supportedLocales(): array
    {
        $configured = config('nuewire.filesystem.supported_locales', ['id', 'en']);
        $locales = is_array($configured) ? $configured : ['id', 'en'];
        $locales = array_values(array_filter(
            array_map(static fn (mixed $locale): string => strtolower(trim((string) $locale)), $locales),
            static fn (string $locale): bool => $locale !== '',
        ));

        return $locales !== [] ? array_values(array_unique($locales)) : ['id', 'en'];
    }

    private function resolveLocale(?string $requested): string
    {
        if (is_string($requested) && trim($requested) !== '') {
            return $this->normalizeLocale($requested);
        }

        if ((bool) config('nuewire.filesystem.remember_locale', true)) {
            try {
                $stored = session()->get((string) config(
                    'nuewire.filesystem.locale_session_key',
                    'nuewire.filesystem.locale',
                ));

                if (is_string($stored) && trim($stored) !== '') {
                    return $this->normalizeLocale($stored);
                }
            } catch (Throwable) {
                // A host route without session middleware can still render the component.
            }
        }

        return $this->normalizeLocale((string) config('nuewire.filesystem.locale', 'id'));
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = strtolower(str_replace('_', '-', trim($locale)));
        $locale = explode('-', $locale)[0] ?? '';
        $supported = $this->supportedLocales();

        if (in_array($locale, $supported, true)) {
            return $locale;
        }

        $configuredDefault = strtolower(trim((string) config('nuewire.filesystem.locale', 'id')));

        return in_array($configuredDefault, $supported, true)
            ? $configuredDefault
            : ($supported[0] ?? 'id');
    }

    private function rememberLocale(): void
    {
        if (! (bool) config('nuewire.filesystem.remember_locale', true)) {
            return;
        }

        try {
            session()->put(
                (string) config('nuewire.filesystem.locale_session_key', 'nuewire.filesystem.locale'),
                $this->locale,
            );
        } catch (Throwable) {
            // Session persistence is optional.
        }
    }

    /**
     * @param array<string, scalar|null> $replace
     */
    private function translate(string $key, array $replace = []): string
    {
        return (string) trans("nuewire-filesystem::filesystem.{$key}", $replace, $this->locale);
    }

    private function ensureAuthorized(string $permission = 'filesystem.view'): void
    {
        $guard = trim((string) config('nuewire.filesystem.authorization.guard', ''));
        $auth = $guard === '' ? Auth::guard() : Auth::guard($guard);
        $user = $auth->user();

        if (app()->bound('nuewire.acl.enabled')) {
            if ($user === null || ! method_exists($user, 'can')) {
                abort(403);
            }

            try {
                abort_unless($user->can($permission), 403);
            } catch (Throwable) {
                abort(403);
            }
        }

        $gate = trim((string) config('nuewire.filesystem.authorization.gate', ''));

        if ($gate !== '') {
            abort_unless(Gate::allows($gate), 403);
            return;
        }

        if ((bool) config('nuewire.filesystem.authorization.require_authenticated_user', true)) {
            $authenticated = $auth->check();
            abort_unless($authenticated, 403);
        }
    }

    private function clearMessages(): void
    {
        $this->status = null;
        $this->error = null;
        $this->testUrl = null;
        $this->resetValidation();
    }

    private function clearSecretInputs(): void
    {
        $this->s3Secret = '';
        $this->bunnyPassword = '';
    }
}
