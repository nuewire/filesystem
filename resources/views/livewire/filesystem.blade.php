@php
    $t = static fn (string $key, array $replace = []): string => (string) trans(
        "nuewire-filesystem::filesystem.{$key}",
        $replace,
        $locale,
    );
@endphp

<div class="nuewirefs" lang="{{ $locale }}" wire:key="nuewire-filesystem-settings">
    <style>
        .nuewirefs { --bfs-border:#d7dce2; --bfs-muted:#667085; --bfs-text:#182230; --bfs-bg:#fff; --bfs-soft:#f7f8fa; --bfs-accent:#2563eb; --bfs-danger:#b42318; --bfs-success:#067647; font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; color:var(--bfs-text); }
        .nuewirefs * { box-sizing:border-box; }
        .nuewirefs__shell { max-width:920px; border:1px solid var(--bfs-border); border-radius:16px; background:var(--bfs-bg); overflow:hidden; box-shadow:0 10px 30px rgba(16,24,40,.06); }
        .nuewirefs__head { padding:24px; border-bottom:1px solid var(--bfs-border); background:linear-gradient(180deg,#fff,var(--bfs-soft)); }
        .nuewirefs__headrow { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; }
        .nuewirefs__heading { min-width:0; }
        .nuewirefs__title { margin:0; font-size:22px; line-height:1.25; }
        .nuewirefs__lead { margin:8px 0 0; color:var(--bfs-muted); line-height:1.55; }
        .nuewirefs__locale { flex:0 0 auto; min-width:150px; }
        .nuewirefs__locale label { display:block; margin-bottom:6px; color:var(--bfs-muted); font-size:12px; font-weight:650; }
        .nuewirefs__locale select { width:100%; min-height:38px; padding:7px 30px 7px 10px; border:1px solid var(--bfs-border); border-radius:9px; background:#fff; color:var(--bfs-text); font:inherit; outline:none; }
        .nuewirefs__locale select:focus { border-color:var(--bfs-accent); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        .nuewirefs__meta { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; }
        .nuewirefs__pill { display:inline-flex; align-items:center; min-height:28px; padding:4px 10px; border:1px solid var(--bfs-border); border-radius:999px; background:#fff; color:var(--bfs-muted); font-size:12px; }
        .nuewirefs__body { padding:24px; }
        .nuewirefs__providers { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .nuewirefs__provider { position:relative; display:block; padding:16px; border:1px solid var(--bfs-border); border-radius:12px; cursor:pointer; background:#fff; transition:border-color .15s ease,box-shadow .15s ease; }
        .nuewirefs__provider:hover { border-color:#98a2b3; }
        .nuewirefs__provider:has(input:checked) { border-color:var(--bfs-accent); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        .nuewirefs__provider input { position:absolute; opacity:0; pointer-events:none; }
        .nuewirefs__provider strong { display:block; font-size:15px; }
        .nuewirefs__provider span { display:block; margin-top:5px; color:var(--bfs-muted); font-size:13px; line-height:1.4; }
        .nuewirefs__section { margin-top:24px; padding-top:24px; border-top:1px solid var(--bfs-border); }
        .nuewirefs__section h3 { margin:0 0 5px; font-size:16px; }
        .nuewirefs__section > p { margin:0 0 16px; color:var(--bfs-muted); font-size:13px; line-height:1.5; }
        .nuewirefs__grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .nuewirefs__field { min-width:0; }
        .nuewirefs__field--full { grid-column:1/-1; }
        .nuewirefs__field label { display:block; margin-bottom:7px; font-size:13px; font-weight:650; }
        .nuewirefs__field input[type=text], .nuewirefs__field input[type=password], .nuewirefs__field input[type=url] { width:100%; min-height:42px; padding:9px 11px; border:1px solid var(--bfs-border); border-radius:9px; background:#fff; color:var(--bfs-text); font:inherit; outline:none; }
        .nuewirefs__field input:focus { border-color:var(--bfs-accent); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        .nuewirefs__hint { margin-top:6px; color:var(--bfs-muted); font-size:12px; line-height:1.45; overflow-wrap:anywhere; }
        .nuewirefs__error { margin-top:6px; color:var(--bfs-danger); font-size:12px; }
        .nuewirefs__check { display:flex; align-items:flex-start; gap:10px; }
        .nuewirefs__check input { margin-top:3px; }
        .nuewirefs__check span { font-size:14px; line-height:1.45; }
        .nuewirefs__notice { margin-top:18px; padding:12px 14px; border-radius:10px; font-size:13px; line-height:1.5; overflow-wrap:anywhere; }
        .nuewirefs__notice--success { background:#ecfdf3; color:var(--bfs-success); border:1px solid #abefc6; }
        .nuewirefs__notice--danger { background:#fef3f2; color:var(--bfs-danger); border:1px solid #fecdca; }
        .nuewirefs__notice--info { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .nuewirefs__actions { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin-top:24px; }
        .nuewirefs__button { min-height:42px; padding:9px 15px; border:1px solid transparent; border-radius:9px; font:inherit; font-weight:650; cursor:pointer; }
        .nuewirefs__button[disabled] { opacity:.55; cursor:wait; }
        .nuewirefs__button--primary { background:var(--bfs-accent); color:#fff; }
        .nuewirefs__button--secondary { border-color:var(--bfs-border); background:#fff; color:var(--bfs-text); }
        .nuewirefs__button--quiet { margin-left:auto; border-color:transparent; background:transparent; color:var(--bfs-muted); }
        @media (max-width:720px) { .nuewirefs__headrow { flex-direction:column; } .nuewirefs__locale { width:100%; } .nuewirefs__providers,.nuewirefs__grid { grid-template-columns:1fr; } .nuewirefs__field--full { grid-column:auto; } .nuewirefs__button--quiet { margin-left:0; } }
    </style>

    <section class="nuewirefs__shell" aria-labelledby="nuewirefs-title">
        <header class="nuewirefs__head">
            <div class="nuewirefs__headrow">
                <div class="nuewirefs__heading">
                    <h2 class="nuewirefs__title" id="nuewirefs-title">{{ $t('title') }}</h2>
                    <p class="nuewirefs__lead">{{ $t('lead') }}</p>
                </div>
                <div class="nuewirefs__locale">
                    <label for="bfs-locale">{{ $t('language.label') }}</label>
                    <select id="bfs-locale" wire:model.live="locale">
                        @foreach ($localeOptions as $localeCode => $localeLabel)
                            <option value="{{ $localeCode }}">{{ $localeLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="nuewirefs__meta">
                <span class="nuewirefs__pill">{{ $t('meta.active_disk', ['disk' => $activeDisk]) }}</span>
                <span class="nuewirefs__pill">{{ $t('meta.base_directory', ['directory' => $activeDirectory !== '' ? $activeDirectory : $t('meta.root')]) }}</span>
                <span class="nuewirefs__pill">{{ $t('meta.config', ['source' => $settingsExist ? $t('meta.encrypted_file') : $t('meta.package_default')]) }}</span>
            </div>
        </header>

        <form class="nuewirefs__body" wire:submit="save">
            <div class="nuewirefs__providers" role="radiogroup" aria-label="{{ $t('providers.aria_label') }}">
                <label class="nuewirefs__provider">
                    <input type="radio" value="local" wire:model.live="driver">
                    <strong>{{ $t('providers.local.name') }}</strong>
                    <span>{{ $t('providers.local.description') }}</span>
                </label>
                <label class="nuewirefs__provider">
                    <input type="radio" value="s3" wire:model.live="driver">
                    <strong>{{ $t('providers.s3.name') }}</strong>
                    <span>{{ $t('providers.s3.description') }}</span>
                </label>
                <label class="nuewirefs__provider">
                    <input type="radio" value="bunnycdn" wire:model.live="driver">
                    <strong>{{ $t('providers.bunnycdn.name') }}</strong>
                    <span>{{ $t('providers.bunnycdn.description') }}</span>
                </label>
            </div>

            @if ($driver === 'local')
                <div class="nuewirefs__section">
                    <h3>{{ $t('local.title') }}</h3>
                    <p>{{ $t('local.description') }}</p>
                    <div class="nuewirefs__field">
                        <label for="bfs-local-directory">{{ $t('local.directory') }}</label>
                        <input id="bfs-local-directory" type="text" wire:model="localDirectory" placeholder="media">
                        <div class="nuewirefs__hint">{{ $t('local.directory_hint') }}</div>
                        @error('localDirectory') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                    </div>
                    @if (! $storageLinkExists)
                        <div class="nuewirefs__notice nuewirefs__notice--info">{{ $t('local.storage_link_missing') }}</div>
                    @endif
                </div>
            @elseif ($driver === 's3')
                <div class="nuewirefs__section">
                    <h3>{{ $t('s3.title') }}</h3>
                    <p>{{ $t('s3.description') }}</p>
                    <div class="nuewirefs__grid">
                        <div class="nuewirefs__field">
                            <label for="bfs-s3-key">{{ $t('s3.key') }}</label>
                            <input id="bfs-s3-key" type="text" autocomplete="off" wire:model="s3Key">
                            @error('s3Key') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field">
                            <label for="bfs-s3-secret">{{ $t('s3.secret') }}</label>
                            <input id="bfs-s3-secret" type="password" autocomplete="new-password" wire:model="s3Secret" placeholder="{{ $hasS3Secret ? $t('secrets.saved') : $t('secrets.required') }}">
                            @error('s3Secret') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field">
                            <label for="bfs-s3-region">{{ $t('s3.region') }}</label>
                            <input id="bfs-s3-region" type="text" wire:model="s3Region" placeholder="ap-southeast-1">
                            @error('s3Region') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field">
                            <label for="bfs-s3-bucket">{{ $t('s3.bucket') }}</label>
                            <input id="bfs-s3-bucket" type="text" wire:model="s3Bucket">
                            @error('s3Bucket') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field nuewirefs__field--full">
                            <label for="bfs-s3-directory">{{ $t('s3.directory') }}</label>
                            <input id="bfs-s3-directory" type="text" wire:model="s3Directory" placeholder="my-app/media">
                            <div class="nuewirefs__hint">{{ $t('s3.directory_hint') }}</div>
                            @error('s3Directory') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field">
                            <label for="bfs-s3-url">{{ $t('s3.url') }}</label>
                            <input id="bfs-s3-url" type="url" wire:model="s3Url" placeholder="https://cdn.example.com">
                            <div class="nuewirefs__hint">{{ $t('s3.url_hint') }}</div>
                            @error('s3Url') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field">
                            <label for="bfs-s3-endpoint">{{ $t('s3.endpoint') }}</label>
                            <input id="bfs-s3-endpoint" type="url" wire:model="s3Endpoint" placeholder="https://s3.example.com">
                            @error('s3Endpoint') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field nuewirefs__field--full">
                            <label class="nuewirefs__check">
                                <input type="checkbox" wire:model="s3PathStyle">
                                <span>{{ $t('s3.path_style') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            @else
                <div class="nuewirefs__section">
                    <h3>{{ $t('bunny.title') }}</h3>
                    <p>{{ $t('bunny.description') }}</p>
                    <div class="nuewirefs__grid">
                        <div class="nuewirefs__field">
                            <label for="bfs-bunny-zone">{{ $t('bunny.zone') }}</label>
                            <input id="bfs-bunny-zone" type="text" autocomplete="off" wire:model="bunnyStorageZone">
                            <div class="nuewirefs__hint">{{ $t('bunny.zone_hint') }}</div>
                            @error('bunnyStorageZone') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field">
                            <label for="bfs-bunny-password">{{ $t('bunny.password') }}</label>
                            <input id="bfs-bunny-password" type="password" autocomplete="new-password" wire:model="bunnyPassword" placeholder="{{ $hasBunnyPassword ? $t('secrets.saved') : $t('secrets.required') }}">
                            @error('bunnyPassword') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field">
                            <label for="bfs-bunny-region">{{ $t('bunny.region') }}</label>
                            <input id="bfs-bunny-region" type="text" wire:model="bunnyRegion" placeholder="auto">
                            <div class="nuewirefs__hint">{{ $t('bunny.region_hint') }}</div>
                            @error('bunnyRegion') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field">
                            <label for="bfs-bunny-endpoint">{{ $t('bunny.endpoint') }}</label>
                            <input id="bfs-bunny-endpoint" type="url" wire:model="bunnyEndpoint" placeholder="https://sg-s3.storage.bunnycdn.com">
                            @error('bunnyEndpoint') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field nuewirefs__field--full">
                            <label for="bfs-bunny-directory">{{ $t('bunny.directory') }}</label>
                            <input id="bfs-bunny-directory" type="text" wire:model="bunnyDirectory" placeholder="my-app/media">
                            <div class="nuewirefs__hint">{{ $t('bunny.directory_hint') }}</div>
                            @error('bunnyDirectory') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nuewirefs__field nuewirefs__field--full">
                            <label for="bfs-bunny-cdn">{{ $t('bunny.cdn_url') }}</label>
                            <input id="bfs-bunny-cdn" type="url" wire:model="bunnyCdnUrl" placeholder="https://your-pull-zone.b-cdn.net">
                            <div class="nuewirefs__hint">{{ $t('bunny.cdn_url_hint') }}</div>
                            @error('bunnyCdnUrl') <div class="nuewirefs__error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            @endif

            <div class="nuewirefs__section">
                <label class="nuewirefs__check">
                    <input type="checkbox" wire:model="setAsDefault">
                    <span>{{ $t('default_disk.label') }}</span>
                </label>
                <div class="nuewirefs__hint">{{ $t('default_disk.hint') }}</div>
            </div>

            @if ($status)
                <div class="nuewirefs__notice nuewirefs__notice--success" role="status">{{ $status }}</div>
            @endif
            @if ($testUrl)
                <div class="nuewirefs__notice nuewirefs__notice--info">{{ $t('notices.test_url', ['url' => $testUrl]) }}</div>
            @endif
            @if ($error)
                <div class="nuewirefs__notice nuewirefs__notice--danger" role="alert">{{ $error }}</div>
            @endif

            <div class="nuewirefs__actions">
                <button class="nuewirefs__button nuewirefs__button--primary" type="submit" wire:loading.attr="disabled" wire:target="save,useLocal">
                    {{ $t('actions.save') }}
                </button>
                <button class="nuewirefs__button nuewirefs__button--secondary" type="button" wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection">
                    {{ $t('actions.test') }}
                </button>
                @if ($driver !== 'local')
                    <button class="nuewirefs__button nuewirefs__button--quiet" type="button" wire:click="useLocal" wire:loading.attr="disabled" wire:target="useLocal">
                        {{ $t('actions.switch_local') }}
                    </button>
                @endif
            </div>

            <div class="nuewirefs__hint" style="margin-top:16px">{{ $t('notices.settings_file', ['path' => $settingsPath]) }}</div>
        </form>
    </section>
</div>
